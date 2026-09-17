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
                'inspector:id,uuid,name,email',
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

            $inspection = Inspection::query()->create([
                'permit_application_id' => $application->id,
                'inspection_no' => $this->nextNumber('inspection', 'IN-'.date('Y').'-'),
                'type' => $data['type'] ?? 'joint_structural',
                'status' => InspectionStatus::Scheduled->value,
                'scheduled_at' => $data['scheduled_at'] ?? now()->addDay(),
                'inspector_id' => $inspector?->id ?? $actor->id,
                'scheduled_by' => $actor->id,
                'location' => $data['location'] ?? $application->project_location,
                'latitude' => $data['latitude'] ?? $application->latitude,
                'longitude' => $data['longitude'] ?? $application->longitude,
                'notes' => $data['notes'] ?? null,
                'compliance_sheet' => $data['compliance_sheet'] ?? [],
                'electrical_form' => $data['electrical_form'] ?? [],
            ]);

            $application->update(['status' => 'for_inspection']);

            $this->audit->log('inspection.scheduled', [
                'inspection_id' => $inspection->uuid,
                'application_id' => $application->uuid,
                'inspection_no' => $inspection->inspection_no,
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

            $inspection->fill([
                'status' => InspectionStatus::Completed->value,
                'result' => $result->value,
                'completed_at' => now(),
                'notes' => $data['notes'] ?? $inspection->notes,
                'compliance_sheet' => $data['compliance_sheet'] ?? $inspection->compliance_sheet,
                'electrical_form' => $data['electrical_form'] ?? $inspection->electrical_form,
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
            ]);

            return $inspection->fresh(['application.user', 'inspector', 'scheduledByUser']) ?? $inspection;
        });

        $this->notifier->inspectionCompleted($completed);

        return $completed;
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
