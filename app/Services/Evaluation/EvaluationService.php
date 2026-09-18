<?php

declare(strict_types=1);

namespace App\Services\Evaluation;

use App\Enums\EvaluationResult;
use App\Enums\RoutingStepStatus;
use App\Models\Evaluation;
use App\Models\EvaluationTimeLog;
use App\Models\PermitApplication;
use App\Models\RoutingSlipStep;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Notification\WorkflowNotifier;
use App\Services\Operations\OperationsWorkflow;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class EvaluationService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly OperationsWorkflow $operations,
        private readonly WorkflowNotifier $notifier,
    ) {
    }

    public function listForStaff(string $search = '', int $perPage = 15, string $bucket = 'active'): LengthAwarePaginator
    {
        $statuses = $bucket === 'completed'
            ? ['for_inspection', 'for_payment', 'for_releasing', 'for_compliance', 'released', 'disapproved']
            : ['submitted', 'under_evaluation'];

        return PermitApplication::query()
            ->with(['user:id,uuid,name,email', 'formDefinition:id,uuid,code,title'])
            ->whereIn('status', $statuses)
            ->when($search !== '', function ($q) use ($search): void {
                $like = '%'.$search.'%';
                $q->where(function ($inner) use ($like): void {
                    $inner->where('application_no', 'like', $like)
                        ->orWhere('project_title', 'like', $like)
                        ->orWhere('project_location', 'like', $like);
                });
            })
            ->when(
                $bucket === 'completed',
                fn ($q) => $q->latest('updated_at'),
                fn ($q) => $q->latest('submitted_at'),
            )
            ->paginate(min(max($perPage, 1), 100));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(PermitApplication $application, User $evaluator, array $data): Evaluation
    {
        $this->operations->assertCanEvaluate($application);

        $step = null;
        if (! empty($data['routing_slip_step_uuid'])) {
            $step = RoutingSlipStep::query()
                ->where('uuid', $data['routing_slip_step_uuid'])
                ->first();
            if (! $step) {
                throw ValidationException::withMessages([
                    'routing_slip_step_uuid' => ['Routing step not found.'],
                ]);
            }
        }

        $evaluation = Evaluation::query()->create([
            'permit_application_id' => $application->id,
            'routing_slip_step_id' => $step?->id,
            'evaluator_id' => $evaluator->id,
            'status' => 'draft',
            'findings' => $data['findings'] ?? [],
            'remarks' => $data['remarks'] ?? null,
        ]);

        $this->audit->log('evaluation.created', [
            'evaluation_id' => $evaluation->uuid,
            'application_id' => $application->uuid,
        ]);

        return $evaluation->load(['evaluator', 'step.department', 'application']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function decide(Evaluation $evaluation, User $evaluator, array $data): Evaluation
    {
        if ((int) $evaluation->evaluator_id !== (int) $evaluator->id && ! $evaluator->can('users.manage')) {
            throw ValidationException::withMessages([
                'evaluation' => ['You can only decide your own evaluation sheets.'],
            ]);
        }

        $decided = DB::transaction(function () use ($evaluation, $data): Evaluation {
            $result = EvaluationResult::from((string) $data['result']);

            $evaluation->fill([
                'status' => 'decided',
                'result' => $result->value,
                'findings' => $data['findings'] ?? $evaluation->findings,
                'remarks' => $data['remarks'] ?? $evaluation->remarks,
                'decided_at' => now(),
            ]);
            $evaluation->save();

            if ($evaluation->routing_slip_step_id) {
                $step = RoutingSlipStep::query()->find($evaluation->routing_slip_step_id);
                if ($step && $step->status !== RoutingStepStatus::Completed) {
                    $step->fill([
                        'status' => RoutingStepStatus::Completed->value,
                        'completed_at' => now(),
                        'started_at' => $step->started_at ?? now(),
                        'notes' => $evaluation->remarks,
                    ]);
                    $step->save();
                }
            }

            $application = $evaluation->application;
            if ($application) {
                $nextStatus = match ($result) {
                    EvaluationResult::Compliant => 'for_inspection',
                    EvaluationResult::NonCompliant => 'for_compliance',
                    EvaluationResult::NeedsInfo => 'under_evaluation',
                };
                $application->update(['status' => $nextStatus]);
            }

            $this->audit->log('evaluation.decided', [
                'evaluation_id' => $evaluation->uuid,
                'result' => $result->value,
                'application_id' => $application?->uuid,
                'next_status' => $application?->status,
            ]);

            return $evaluation->fresh(['evaluator', 'step.department', 'application.user']) ?? $evaluation;
        });

        $this->notifier->evaluationDecided($decided);

        return $decided;
    }

    public function startTimer(PermitApplication $application, User $user, ?string $notes = null): EvaluationTimeLog
    {
        $open = EvaluationTimeLog::query()
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->first();

        if ($open) {
            throw ValidationException::withMessages([
                'timer' => ['You already have an open time log. Stop it before starting another.'],
            ]);
        }

        $log = EvaluationTimeLog::query()->create([
            'permit_application_id' => $application->id,
            'user_id' => $user->id,
            'started_at' => now(),
            'notes' => $notes,
        ]);

        $this->audit->log('evaluation_time.started', [
            'time_log_id' => $log->uuid,
            'application_id' => $application->uuid,
        ]);

        return $log;
    }

    public function stopTimer(User $user): EvaluationTimeLog
    {
        $open = EvaluationTimeLog::query()
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->first();

        if (! $open) {
            throw ValidationException::withMessages([
                'timer' => ['No open time log found.'],
            ]);
        }

        $ended = now();
        $open->fill([
            'ended_at' => $ended,
            'duration_seconds' => max(0, $ended->diffInSeconds($open->started_at)),
        ]);
        $open->save();

        $this->audit->log('evaluation_time.stopped', [
            'time_log_id' => $open->uuid,
            'duration_seconds' => $open->duration_seconds,
        ]);

        return $open->refresh();
    }

    public function currentOpenTimer(User $user): ?EvaluationTimeLog
    {
        return EvaluationTimeLog::query()
            ->with(['application:id,uuid,application_no'])
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->first();
    }
}
