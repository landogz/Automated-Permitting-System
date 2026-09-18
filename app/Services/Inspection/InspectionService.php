<?php

declare(strict_types=1);

namespace App\Services\Inspection;

use App\Enums\InspectionResult;
use App\Enums\InspectionStatus;
use App\Models\Inspection;
use App\Models\NumberingSeries;
use App\Models\PermitApplication;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Notification\WorkflowNotifier;
use App\Services\Operations\OperationsWorkflow;
use App\Support\Inspection\InspectionFormCatalog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class InspectionService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly OperationsWorkflow $operations,
        private readonly WorkflowNotifier $notifier,
    ) {
    }

    public function list(string $search = '', ?string $status = null, int $perPage = 25, ?string $bucket = null): LengthAwarePaginator
    {
        return Inspection::query()
            ->with([
                'application:id,uuid,application_no,project_title,status',
                'inspector:id,uuid,name,email,avatar_path',
            ])
            ->when($bucket === 'active', function ($q): void {
                $q->whereIn('status', [
                    InspectionStatus::Scheduled->value,
                    InspectionStatus::InProgress->value,
                ]);
            })
            ->when($bucket === 'completed', function ($q): void {
                $q->whereIn('status', [
                    InspectionStatus::Completed->value,
                    InspectionStatus::Cancelled->value,
                ]);
            })
            ->when(
                ($bucket === null || $bucket === '')
                    && $status !== null
                    && $status !== ''
                    && $status !== 'all',
                fn ($q) => $q->where('status', $status),
            )
            ->when($search !== '', function ($q) use ($search): void {
                $like = '%'.$search.'%';
                $q->where(function ($inner) use ($like): void {
                    $inner->where('inspection_no', 'like', $like)
                        ->orWhere('location', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhereHas('application', function ($app) use ($like): void {
                            $app->where('application_no', 'like', $like)
                                ->orWhere('project_title', 'like', $like);
                        });
                });
            })
            ->latest('scheduled_at')
            ->paginate(min(max($perPage, 1), 100));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function schedule(PermitApplication $application, User $actor, array $data): Inspection
    {
        $this->operations->assertCanScheduleInspection($application);
        $this->assertNoOpenInspection($application);

        $inspection = DB::transaction(function () use ($application, $actor, $data): Inspection {
            // Re-check inside the transaction to avoid duplicate open tickets under race.
            $this->assertNoOpenInspection($application);

            $inspector = null;
            if (! empty($data['inspector_uuid'])) {
                $inspector = User::query()->where('uuid', $data['inspector_uuid'])->first();
                if (! $inspector) {
                    throw ValidationException::withMessages([
                        'inspector_uuid' => ['Inspector not found.'],
                    ]);
                }
            }

            $team = InspectionFormCatalog::normalizeTeamInspectors($data['team_inspectors'] ?? []);
            $scheduleSheet = $this->buildScheduleSheet($data, $application);

            $inspection = Inspection::query()->create([
                'permit_application_id' => $application->id,
                'inspection_no' => $this->nextNumber('inspection', 'IN-'.date('Y').'-'),
                'type' => $data['type'] ?? 'joint_structural',
                'status' => InspectionStatus::Scheduled->value,
                'scheduled_at' => $data['scheduled_at'] ?? now()->addDay(),
                'inspector_id' => $inspector?->id ?? $actor->id,
                'team_inspectors' => $team,
                'scheduled_by' => $actor->id,
                'location' => $data['location'] ?? $application->project_location,
                'latitude' => $data['latitude'] ?? $application->latitude,
                'longitude' => $data['longitude'] ?? $application->longitude,
                'notes' => $data['notes'] ?? null,
                'schedule_sheet' => $scheduleSheet,
                'inspector_notes' => InspectionFormCatalog::blankInspectorNotes(),
                'compliance_sheet' => InspectionFormCatalog::normalizeComplianceSheet(
                    $data['compliance_sheet'] ?? ['items' => InspectionFormCatalog::qms65DefaultItems()],
                ),
                'electrical_form' => InspectionFormCatalog::normalizeElectricalForm(
                    $data['electrical_form'] ?? InspectionFormCatalog::blankElectricalForm(),
                ),
            ]);

            $application->update(['status' => 'for_inspection']);

            $this->audit->log('inspection.scheduled', [
                'inspection_id' => $inspection->uuid,
                'application_id' => $application->uuid,
                'inspection_no' => $inspection->inspection_no,
                'type' => $inspection->type,
                'team_count' => count($team),
                'has_schedule_sheet' => filled($scheduleSheet['purpose'] ?? null),
            ]);

            return $inspection->load(['application.user', 'inspector', 'scheduledByUser']);
        });

        $this->notifier->inspectionScheduled($inspection);

        return $inspection;
    }

    /**
     * One open (scheduled / in-progress) inspection per application — avoids duplicate rows on /admin/inspections.
     */
    private function assertNoOpenInspection(PermitApplication $application): void
    {
        $openExists = Inspection::query()
            ->where('permit_application_id', $application->id)
            ->whereIn('status', [
                InspectionStatus::Scheduled->value,
                InspectionStatus::InProgress->value,
            ])
            ->exists();

        if ($openExists) {
            throw ValidationException::withMessages([
                'application' => [
                    'This application already has a scheduled or in-progress inspection. Complete or cancel it before scheduling another.',
                ],
            ]);
        }
    }

    /**
     * Save O-03 / QMS-65 / DPWH sheets without completing the inspection.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveForms(Inspection $inspection, User $actor, array $data): Inspection
    {
        if (in_array($inspection->status, [InspectionStatus::Completed, InspectionStatus::Cancelled], true)) {
            throw ValidationException::withMessages([
                'inspection' => ['Completed or cancelled inspections cannot be edited.'],
            ]);
        }

        return DB::transaction(function () use ($inspection, $actor, $data): Inspection {
            $inspectorNotes = array_key_exists('inspector_notes', $data)
                ? InspectionFormCatalog::normalizeInspectorNotes($data['inspector_notes'])
                : InspectionFormCatalog::normalizeInspectorNotes($inspection->inspector_notes);

            if (
                ($inspectorNotes['findings'] ?? '') === ''
                && filled($data['notes'] ?? null)
            ) {
                $inspectorNotes['findings'] = (string) $data['notes'];
            }

            $compliance = array_key_exists('compliance_sheet', $data)
                ? InspectionFormCatalog::normalizeComplianceSheet($data['compliance_sheet'])
                : InspectionFormCatalog::normalizeComplianceSheet($inspection->compliance_sheet);

            $electrical = array_key_exists('electrical_form', $data)
                ? InspectionFormCatalog::normalizeElectricalForm($data['electrical_form'])
                : InspectionFormCatalog::normalizeElectricalForm($inspection->electrical_form);

            $team = array_key_exists('team_inspectors', $data)
                ? InspectionFormCatalog::normalizeTeamInspectors($data['team_inspectors'])
                : InspectionFormCatalog::normalizeTeamInspectors($inspection->team_inspectors);

            $fill = [
                'status' => InspectionStatus::InProgress->value,
                'notes' => array_key_exists('notes', $data) ? ($data['notes'] ?: $inspection->notes) : $inspection->notes,
                'inspector_notes' => $inspectorNotes,
                'compliance_sheet' => $compliance,
                'electrical_form' => $electrical,
                'team_inspectors' => $team,
                'inspector_id' => $inspection->inspector_id ?: $actor->id,
            ];

            $inspection->fill($fill);
            $inspection->save();

            $this->audit->log('inspection.forms_saved', [
                'inspection_id' => $inspection->uuid,
                'application_id' => $inspection->application?->uuid,
                'status' => InspectionStatus::InProgress->value,
                'qms65_item_count' => count($compliance['items'] ?? []),
                'has_o03' => filled($inspectorNotes['findings'] ?? null),
                'has_electrical_form' => ($electrical['result'] ?? 'na') !== 'na',
            ]);

            return $inspection->fresh(['application.user', 'inspector', 'scheduledByUser']) ?? $inspection;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function complete(Inspection $inspection, User $actor, array $data): Inspection
    {
        if ($inspection->status === InspectionStatus::Completed) {
            throw ValidationException::withMessages([
                'inspection' => ['Inspection already completed.'],
            ]);
        }

        $completed = DB::transaction(function () use ($inspection, $actor, $data): Inspection {
            $result = InspectionResult::from((string) $data['result']);

            $inspectorNotes = array_key_exists('inspector_notes', $data)
                ? InspectionFormCatalog::normalizeInspectorNotes($data['inspector_notes'])
                : InspectionFormCatalog::normalizeInspectorNotes($inspection->inspector_notes);

            if (
                ($inspectorNotes['findings'] ?? '') === ''
                && filled($data['notes'] ?? null)
            ) {
                $inspectorNotes['findings'] = (string) $data['notes'];
            }

            $compliance = array_key_exists('compliance_sheet', $data)
                ? InspectionFormCatalog::normalizeComplianceSheet($data['compliance_sheet'])
                : InspectionFormCatalog::normalizeComplianceSheet($inspection->compliance_sheet);

            $electrical = array_key_exists('electrical_form', $data)
                ? InspectionFormCatalog::normalizeElectricalForm($data['electrical_form'])
                : InspectionFormCatalog::normalizeElectricalForm($inspection->electrical_form);

            $team = array_key_exists('team_inspectors', $data)
                ? InspectionFormCatalog::normalizeTeamInspectors($data['team_inspectors'])
                : InspectionFormCatalog::normalizeTeamInspectors($inspection->team_inspectors);

            $inspection->fill([
                'status' => InspectionStatus::Completed->value,
                'result' => $result->value,
                'completed_at' => now(),
                'notes' => $data['notes'] ?? $inspection->notes,
                'inspector_notes' => $inspectorNotes,
                'compliance_sheet' => $compliance,
                'electrical_form' => $electrical,
                'team_inspectors' => $team,
                'inspector_id' => $inspection->inspector_id ?: $actor->id,
            ]);
            $inspection->save();

            $application = $inspection->application;
            if ($application) {
                $application->update([
                    'status' => match ($result) {
                        InspectionResult::Failed => 'for_compliance',
                        InspectionResult::Passed, InspectionResult::Conditional => 'for_payment',
                    },
                ]);
            }

            $this->audit->log('inspection.completed', [
                'inspection_id' => $inspection->uuid,
                'result' => $result->value,
                'application_id' => $application?->uuid,
                'next_status' => $application?->status,
                'has_failure_notes' => $result === InspectionResult::Failed && filled($data['notes'] ?? null),
                'qms65_item_count' => count($compliance['items'] ?? []),
                'has_o03' => filled($inspectorNotes['findings'] ?? null),
                'has_electrical_form' => ($electrical['result'] ?? 'na') !== 'na',
            ]);

            return $inspection->fresh(['application.user', 'inspector', 'scheduledByUser']) ?? $inspection;
        });

        $this->notifier->inspectionCompleted($completed);

        return $completed;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function buildScheduleSheet(array $data, PermitApplication $application): array
    {
        $incoming = is_array($data['schedule_sheet'] ?? null) ? $data['schedule_sheet'] : [];
        $disciplines = $incoming['disciplines'] ?? [];
        if (! is_array($disciplines) || $disciplines === []) {
            $type = strtolower((string) ($data['type'] ?? 'joint_structural'));
            $disciplines = match (true) {
                str_contains($type, 'architectural') => ['architectural'],
                str_contains($type, 'electrical') => ['electrical'],
                str_contains($type, 'sanitary') => ['sanitary'],
                str_contains($type, 'mechanical') => ['mechanical'],
                str_contains($type, 'fire') => ['fire_safety'],
                $type === 'electrical' => ['electrical'],
                $type === 'final' => ['structural', 'architectural', 'electrical', 'sanitary', 'mechanical', 'fire_safety'],
                default => ['structural'],
            };
        }

        return [
            'form_code' => 'QMS-38',
            'purpose' => (string) ($incoming['purpose'] ?? 'Joint site inspection'),
            'meeting_point' => (string) ($incoming['meeting_point'] ?? ($data['location'] ?? $application->project_location ?? '')),
            'disciplines' => array_values(array_filter(array_map(
                static fn ($d): string => trim((string) $d),
                $disciplines,
            ))),
            'remarks' => (string) ($incoming['remarks'] ?? ''),
            'coordination_notes' => (string) ($incoming['coordination_notes'] ?? ''),
        ];
    }

    private function nextNumber(string $key, string $prefix): string
    {
        $series = NumberingSeries::query()->where('key', $key)->lockForUpdate()->first();
        if (! $series) {
            NumberingSeries::query()->create([
                'key' => $key,
                'prefix' => $prefix,
                'next_number' => 1,
                'pad_length' => 6,
            ]);
            $series = NumberingSeries::query()->where('key', $key)->lockForUpdate()->firstOrFail();
        }

        $number = $series->prefix.str_pad((string) $series->next_number, $series->pad_length, '0', STR_PAD_LEFT);
        $series->update(['next_number' => $series->next_number + 1]);

        return $number;
    }
}
