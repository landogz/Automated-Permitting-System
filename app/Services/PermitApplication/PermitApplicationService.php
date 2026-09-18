<?php

declare(strict_types=1);

namespace App\Services\PermitApplication;

use App\Models\ApplicationDocument;
use App\Models\NumberingSeries;
use App\Models\PermitApplication;
use App\Models\User;
use App\Repositories\FormDefinitionRepository;
use App\Repositories\PermitApplicationRepository;
use App\Services\Audit\AuditLogger;
use App\Services\Integrations\CitizensPortal\CitizensPortalAdapter;
use App\Services\Notification\WorkflowNotifier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PermitApplicationService
{
    /** Applicant may revise filing details / documents before irreversible office outcomes. */
    private const APPLICANT_EDITABLE_STATUSES = [
        'draft',
        'submitted',
        'under_evaluation',
        'for_compliance',
    ];

    public function __construct(
        private readonly PermitApplicationRepository $repository,
        private readonly FormDefinitionRepository $forms,
        private readonly AuditLogger $audit,
        private readonly CitizensPortalAdapter $citizensPortal,
        private readonly WorkflowNotifier $notifier,
    ) {
    }

    public function listForUser(User $user, string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateForUser($user, $search, $perPage);
    }

    /**
     * Staff-facing searchable application list for form pickers.
     *
     * @param  list<string>|null  $statuses
     */
    /**
     * @param  list<string>|null  $statuses
     */
    public function lookupForStaff(
        string $search = '',
        int $perPage = 20,
        ?array $statuses = null,
        bool $excludeWithOpenInspection = false,
    ): LengthAwarePaginator {
        return $this->repository->paginateLookup($search, $perPage, $statuses, $excludeWithOpenInspection);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDraft(User $user, array $data): PermitApplication
    {
        return DB::transaction(function () use ($user, $data): PermitApplication {
            $form = null;
            if (! empty($data['form_definition_uuid'])) {
                $form = $this->forms->findByUuid((string) $data['form_definition_uuid']);
                if (! $form || ! $form->is_active) {
                    throw ValidationException::withMessages([
                        'form_definition_uuid' => ['Selected form is invalid or inactive.'],
                    ]);
                }
            }

            $application = $this->repository->create([
                'application_no' => $this->nextApplicationNumber(),
                'user_id' => $user->id,
                'form_definition_id' => $form?->id,
                'status' => 'draft',
                'project_title' => $data['project_title'] ?? null,
                'project_location' => $data['project_location'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'payload' => $data['payload'] ?? [],
            ]);

            $this->audit->log('permit_application.created', [
                'application_id' => $application->uuid,
                'application_no' => $application->application_no,
            ]);

            return $application->load(['formDefinition', 'documents']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDraft(PermitApplication $application, User $user, array $data): PermitApplication
    {
        $this->assertOwner($application, $user);
        $this->assertApplicantCanEdit($application);

        return DB::transaction(function () use ($application, $data): PermitApplication {
            // Form type is locked after create — ignore form_definition_uuid on updates.
            unset($data['form_definition_uuid'], $data['form_definition_id']);

            $application = $this->repository->update($application, array_intersect_key($data, array_flip([
                'project_title',
                'project_location',
                'latitude',
                'longitude',
                'payload',
            ])));

            $this->audit->log('permit_application.updated', [
                'application_id' => $application->uuid,
                'application_no' => $application->application_no,
                'status' => $application->status,
            ]);

            return $application->load(['formDefinition', 'documents']);
        });
    }

    public function uploadDocument(
        PermitApplication $application,
        User $user,
        string $label,
        UploadedFile $file,
    ): ApplicationDocument {
        $this->assertOwner($application, $user);
        $this->assertApplicantCanEdit($application);

        $label = Str::lower(trim($label));
        if ($label === '' || ! preg_match('/^[a-z][a-z0-9_]*$/', $label)) {
            throw ValidationException::withMessages([
                'label' => ['Invalid attachment key.'],
            ]);
        }

        return DB::transaction(function () use ($application, $label, $file): ApplicationDocument {
            $disk = 'local';
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
            $directory = sprintf('applications/%s/documents', $application->uuid);
            $filename = (string) Str::ulid().'.'.$extension;
            $path = $file->storeAs($directory, $filename, $disk);

            if ($path === false) {
                throw ValidationException::withMessages([
                    'file' => ['Unable to store the uploaded file.'],
                ]);
            }

            $existing = ApplicationDocument::query()
                ->where('permit_application_id', $application->id)
                ->where('label', $label)
                ->first();

            if ($existing) {
                Storage::disk($existing->disk)->delete($existing->path);
                $existing->update([
                    'disk' => $disk,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize() ?: 0,
                ]);
                $document = $existing->fresh() ?? $existing;
            } else {
                $document = ApplicationDocument::query()->create([
                    'permit_application_id' => $application->id,
                    'label' => $label,
                    'disk' => $disk,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize() ?: 0,
                ]);
            }

            $this->audit->log('permit_application.document_uploaded', [
                'application_id' => $application->uuid,
                'application_no' => $application->application_no,
                'document_id' => $document->uuid,
                'label' => $label,
                'mime_type' => $document->mime_type,
                'size' => $document->size,
            ]);

            return $document;
        });
    }

    public function deleteDocument(
        PermitApplication $application,
        ApplicationDocument $document,
        User $user,
    ): void {
        $this->assertOwner($application, $user);
        $this->assertApplicantCanEdit($application);

        if ((int) $document->permit_application_id !== (int) $application->id) {
            throw ValidationException::withMessages([
                'document' => ['Document does not belong to this application.'],
            ]);
        }

        DB::transaction(function () use ($application, $document): void {
            $meta = [
                'application_id' => $application->uuid,
                'application_no' => $application->application_no,
                'document_id' => $document->uuid,
                'label' => $document->label,
            ];

            Storage::disk($document->disk)->delete($document->path);
            $document->delete();

            $this->audit->log('permit_application.document_deleted', $meta);
        });
    }

    public function submit(PermitApplication $application, User $user): PermitApplication
    {
        $this->assertOwner($application, $user);

        if ($application->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => ['Application already submitted.'],
            ]);
        }

        if (blank($application->project_title) || blank($application->project_location)) {
            throw ValidationException::withMessages([
                'project_title' => ['Project title and location are required before submit.'],
            ]);
        }

        $application->loadMissing(['formDefinition', 'documents']);
        $required = $application->formDefinition?->required_attachments ?? [];
        if (is_array($required) && $required !== []) {
            $uploaded = $application->documents
                ->pluck('label')
                ->map(fn ($item) => Str::lower((string) $item))
                ->all();
            $missing = [];
            foreach ($required as $label) {
                $key = Str::lower((string) $label);
                if ($key !== '' && ! in_array($key, $uploaded, true)) {
                    $missing[] = $key;
                }
            }
            if ($missing !== []) {
                throw ValidationException::withMessages([
                    'documents' => ['Upload required attachments before submit: '.implode(', ', $missing)],
                ]);
            }
        }

        $application = DB::transaction(function () use ($application, $user): PermitApplication {
            $application = $this->repository->update($application, [
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            $this->citizensPortal->syncApplicationSubmitted([
                'application_no' => $application->application_no,
                'citizen_email' => $user->email,
                'citizen_name' => $user->name,
            ]);

            $this->audit->log('permit_application.submitted', [
                'application_id' => $application->uuid,
                'application_no' => $application->application_no,
            ]);

            return $application->load(['formDefinition', 'documents']);
        });

        $this->notifier->applicationSubmitted($application, $user);

        return $application;
    }

    /**
     * Stream a document inline (PDF/image preview) or as attachment download.
     * Allowed for the owning applicant or Operations / applications staff.
     */
    public function streamDocument(
        PermitApplication $application,
        ApplicationDocument $document,
        User $user,
        bool $download = false,
    ): StreamedResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse {
        $this->assertDocumentAccess($application, $document, $user);

        if (! Storage::disk($document->disk)->exists($document->path)) {
            abort(404, 'Document file not found.');
        }

        $mime = $document->mime_type ?: 'application/octet-stream';
        $filename = $document->original_name ?: basename($document->path);

        return Storage::disk($document->disk)->response(
            $document->path,
            $this->safeDownloadName($filename),
            [
                'Content-Type' => $mime,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store, max-age=0',
            ],
            $download ? 'attachment' : 'inline',
        );
    }

    public function showForUser(string $uuid, User $user): PermitApplication
    {
        $application = $this->repository->findByUuidForUser($uuid, $user);
        if (! $application) {
            throw ValidationException::withMessages([
                'application' => ['Application not found.'],
            ]);
        }

        $this->audit->log('permit_application.viewed', [
            'application_id' => $application->uuid,
            'application_no' => $application->application_no,
        ]);

        return $application;
    }

    /**
     * Staff/ops detail view (full form payload + documents).
     */
    public function showForStaff(string $uuid): PermitApplication
    {
        $application = $this->repository->findByUuid($uuid);
        if (! $application) {
            throw ValidationException::withMessages([
                'application' => ['Application not found.'],
            ]);
        }

        $this->audit->log('permit_application.staff_viewed', [
            'application_id' => $application->uuid,
            'application_no' => $application->application_no,
        ]);

        return $application->loadMissing([
            'formDefinition',
            'documents',
            'user:id,uuid,name,email,phone',
            'evaluations',
            'inspections',
            'ordersOfPayment',
            'complianceNotices.inspection',
            'complianceNotices.appeals',
            'routingSlips.steps.department',
            'routingSlips.template:id,uuid,code,name,classification',
        ]);
    }

    private function assertDocumentAccess(
        PermitApplication $application,
        ApplicationDocument $document,
        User $user,
    ): void {
        if ((int) $document->permit_application_id !== (int) $application->id) {
            abort(404);
        }

        $isOwner = (int) $application->user_id === (int) $user->id;
        $isStaff = $user->can('applications.manage')
            || $user->can('evaluations.manage')
            || $user->can('inspections.manage')
            || $user->can('fees.manage')
            || $user->can('compliance.manage')
            || $user->can('records.manage');

        if (! $isOwner && ! $isStaff) {
            abort(403, 'You are not allowed to view this document.');
        }
    }

    private function safeDownloadName(string $filename): string
    {
        $clean = preg_replace('/[\r\n"\\\\]/', '_', $filename) ?: 'document';

        return substr($clean, 0, 180);
    }

    private function assertOwner(PermitApplication $application, User $user): void
    {
        if ((int) $application->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'application' => ['You are not allowed to access this application.'],
            ]);
        }
    }

    private function assertApplicantCanEdit(PermitApplication $application): void
    {
        if (! in_array((string) $application->status, self::APPLICANT_EDITABLE_STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => [
                    'This application can no longer be edited. Contact OCBO if you need to correct documents.',
                ],
            ]);
        }
    }

    private function nextApplicationNumber(): string
    {
        $series = NumberingSeries::query()->where('key', 'permit_application')->lockForUpdate()->first();
        if (! $series) {
            $series = NumberingSeries::query()->create([
                'key' => 'permit_application',
                'prefix' => 'APICS-'.date('Y').'-',
                'next_number' => 1,
                'pad_length' => 6,
            ]);
            $series = NumberingSeries::query()->where('key', 'permit_application')->lockForUpdate()->firstOrFail();
        }

        $number = $series->prefix.str_pad((string) $series->next_number, $series->pad_length, '0', STR_PAD_LEFT);
        $series->update(['next_number' => $series->next_number + 1]);

        return $number;
    }
}
