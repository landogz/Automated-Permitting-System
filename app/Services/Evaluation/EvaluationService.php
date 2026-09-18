<?php

declare(strict_types=1);

namespace App\Services\Evaluation;

use App\Enums\EvaluationResult;
use App\Enums\RoutingStepStatus;
use App\Models\Department;
use App\Models\Evaluation;
use App\Models\EvaluationTimeLog;
use App\Models\PermitApplication;
use App\Models\RoutingSlipStep;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Notification\WorkflowNotifier;
use App\Services\Operations\OperationsWorkflow;
use App\Support\Evaluation\EvaluationFormCatalog;
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
     * Create or continue the single open draft evaluation sheet for an application.
     * Repeated "Evaluate" / Save only calls update the same draft instead of stacking rows.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(PermitApplication $application, User $evaluator, array $data): Evaluation
    {
        $this->operations->assertCanEvaluate($application);

        $step = $this->resolveStep($data['routing_slip_step_uuid'] ?? null);
        $findings = EvaluationFormCatalog::normalizeFindings($data['findings'] ?? []);
        $remarks = array_key_exists('remarks', $data)
            ? ($data['remarks'] ?: null)
            : ($findings['overall_remarks'] ?: null);

        $draft = Evaluation::query()
            ->where('permit_application_id', $application->id)
            ->where('status', 'draft')
            ->orderByDesc('id')
            ->first();

        if ($draft) {
            Evaluation::query()
                ->where('permit_application_id', $application->id)
                ->where('status', 'draft')
                ->where('id', '!=', $draft->id)
                ->delete();

            $canEdit = (int) $draft->evaluator_id === (int) $evaluator->id || $evaluator->can('users.manage');
            if (! $canEdit) {
                throw ValidationException::withMessages([
                    'evaluation' => ['An open draft evaluation already exists for another evaluator.'],
                ]);
            }

            if (array_key_exists('findings', $data)) {
                $draft->findings = $findings;
            }
            if (array_key_exists('remarks', $data)) {
                $draft->remarks = $remarks ?: $draft->remarks;
            } elseif ($draft->remarks === null && $remarks) {
                $draft->remarks = $remarks;
            }
            if ($step) {
                $draft->routing_slip_step_id = $step->id;
            }
            $draft->save();

            $this->audit->log('evaluation.draft_continued', [
                'evaluation_id' => $draft->uuid,
                'application_id' => $application->uuid,
                'qms63_item_count' => count(($draft->findings['completeness'] ?? [])),
                'qms64_item_count' => count(($draft->findings['technical'] ?? [])),
            ]);

            return $draft->fresh(['evaluator', 'step.department', 'application']) ?? $draft;
        }

        $evaluation = Evaluation::query()->create([
            'permit_application_id' => $application->id,
            'routing_slip_step_id' => $step?->id,
            'evaluator_id' => $evaluator->id,
            'status' => 'draft',
            'findings' => $findings,
            'remarks' => $remarks,
        ]);

        $this->audit->log('evaluation.created', [
            'evaluation_id' => $evaluation->uuid,
            'application_id' => $application->uuid,
            'qms63_item_count' => count($findings['completeness'] ?? []),
            'qms64_item_count' => count($findings['technical'] ?? []),
            'routing_step_id' => $step?->uuid,
        ]);

        return $evaluation->load(['evaluator', 'step.department', 'application']);
    }

    /**
     * Keep at most one open draft per application (removes accidental duplicates).
     */
    public function pruneOrphanDrafts(PermitApplication $application): int
    {
        $keepId = Evaluation::query()
            ->where('permit_application_id', $application->id)
            ->where('status', 'draft')
            ->orderByDesc('id')
            ->value('id');

        if (! $keepId) {
            return 0;
        }

        return Evaluation::query()
            ->where('permit_application_id', $application->id)
            ->where('status', 'draft')
            ->where('id', '!=', $keepId)
            ->delete();
    }

    /**
     * Save draft findings without deciding (QMS-63/64).
     *
     * @param  array<string, mixed>  $data
     */
    public function saveDraft(Evaluation $evaluation, User $evaluator, array $data): Evaluation
    {
        if ((int) $evaluation->evaluator_id !== (int) $evaluator->id && ! $evaluator->can('users.manage')) {
            throw ValidationException::withMessages([
                'evaluation' => ['You can only edit your own evaluation sheets.'],
            ]);
        }

        if ($evaluation->status === 'decided') {
            throw ValidationException::withMessages([
                'evaluation' => ['Decided evaluation sheets cannot be edited.'],
            ]);
        }

        $findings = array_key_exists('findings', $data)
            ? EvaluationFormCatalog::normalizeFindings($data['findings'])
            : EvaluationFormCatalog::normalizeFindings($evaluation->findings);

        $evaluation->fill([
            'findings' => $findings,
            'remarks' => array_key_exists('remarks', $data)
                ? ($data['remarks'] ?: $evaluation->remarks)
                : $evaluation->remarks,
        ]);
        $evaluation->save();

        $this->audit->log('evaluation.forms_saved', [
            'evaluation_id' => $evaluation->uuid,
            'application_id' => $evaluation->application?->uuid,
            'qms63_item_count' => count($findings['completeness'] ?? []),
            'qms64_item_count' => count($findings['technical'] ?? []),
        ]);

        return $evaluation->fresh(['evaluator', 'step.department', 'application']) ?? $evaluation;
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
            $findings = array_key_exists('findings', $data)
                ? EvaluationFormCatalog::normalizeFindings($data['findings'])
                : EvaluationFormCatalog::normalizeFindings($evaluation->findings);

            $evaluation->fill([
                'status' => 'decided',
                'result' => $result->value,
                'findings' => $findings,
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
                'qms63_item_count' => count($findings['completeness'] ?? []),
                'qms64_item_count' => count($findings['technical'] ?? []),
            ]);

            return $evaluation->fresh(['evaluator', 'step.department', 'application.user']) ?? $evaluation;
        });

        $this->notifier->evaluationDecided($decided);

        return $decided;
    }

    /**
     * @param  array{notes?: string|null, department_uuid?: string|null, routing_slip_step_uuid?: string|null}  $options
     */
    public function startTimer(PermitApplication $application, User $user, array $options = []): EvaluationTimeLog
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

        $step = $this->resolveStep($options['routing_slip_step_uuid'] ?? null);
        $departmentId = $step?->department_id;
        if (! $departmentId && ! empty($options['department_uuid'])) {
            $departmentId = Department::query()->where('uuid', $options['department_uuid'])->value('id');
        }
        if (! $departmentId) {
            $departmentId = $user->department_id;
        }

        $log = EvaluationTimeLog::query()->create([
            'permit_application_id' => $application->id,
            'user_id' => $user->id,
            'department_id' => $departmentId,
            'routing_slip_step_id' => $step?->id,
            'started_at' => now(),
            'notes' => $options['notes'] ?? null,
        ]);

        $this->audit->log('evaluation_time.started', [
            'time_log_id' => $log->uuid,
            'application_id' => $application->uuid,
            'department_id' => $departmentId,
            'routing_step_id' => $step?->uuid,
        ]);

        return $log->load(['department:id,uuid,code,name', 'routingSlipStep:id,uuid,label']);
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
            'department_id' => $open->department_id,
        ]);

        return $open->refresh()->load(['department:id,uuid,code,name', 'user:id,uuid,name']);
    }

    public function currentOpenTimer(User $user): ?EvaluationTimeLog
    {
        return EvaluationTimeLog::query()
            ->with([
                'application:id,uuid,application_no',
                'department:id,uuid,code,name',
                'routingSlipStep:id,uuid,label',
            ])
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->first();
    }

    /**
     * Roll up closed time logs by department and staff.
     *
     * @return array{by_department: list<array<string, mixed>>, by_staff: list<array<string, mixed>>, total_seconds: int}
     */
    public function timeSummary(?string $applicationUuid = null): array
    {
        $base = EvaluationTimeLog::query()
            ->whereNotNull('ended_at')
            ->when($applicationUuid, function ($q) use ($applicationUuid): void {
                $q->whereHas('application', fn ($app) => $app->where('uuid', $applicationUuid));
            });

        $deptRows = (clone $base)
            ->selectRaw('department_id, SUM(duration_seconds) as total_seconds, COUNT(*) as sessions')
            ->groupBy('department_id')
            ->get();

        $departments = Department::query()
            ->whereIn('id', $deptRows->pluck('department_id')->filter()->all())
            ->get(['id', 'uuid', 'code', 'name'])
            ->keyBy('id');

        $byDepartment = $deptRows->map(function ($row) use ($departments): array {
            $dept = $row->department_id ? $departments->get((int) $row->department_id) : null;

            return [
                'department' => $dept ? [
                    'uuid' => $dept->uuid,
                    'code' => $dept->code,
                    'name' => $dept->name,
                ] : [
                    'uuid' => null,
                    'code' => 'UNASSIGNED',
                    'name' => 'Unassigned',
                ],
                'total_seconds' => (int) $row->total_seconds,
                'sessions' => (int) $row->sessions,
            ];
        })->values()->all();

        $staffRows = (clone $base)
            ->selectRaw('user_id, SUM(duration_seconds) as total_seconds, COUNT(*) as sessions')
            ->groupBy('user_id')
            ->get();

        $users = User::query()
            ->whereIn('id', $staffRows->pluck('user_id')->filter()->all())
            ->get(['id', 'uuid', 'name', 'email'])
            ->keyBy('id');

        $byStaff = $staffRows->map(function ($row) use ($users): array {
            $user = $users->get((int) $row->user_id);

            return [
                'user' => $user ? [
                    'uuid' => $user->uuid,
                    'name' => $user->name,
                    'email' => $user->email,
                ] : null,
                'total_seconds' => (int) $row->total_seconds,
                'sessions' => (int) $row->sessions,
            ];
        })->values()->all();

        return [
            'by_department' => $byDepartment,
            'by_staff' => $byStaff,
            'total_seconds' => (int) (clone $base)->sum('duration_seconds'),
        ];
    }

    private function resolveStep(mixed $uuid): ?RoutingSlipStep
    {
        if (! is_string($uuid) || $uuid === '') {
            return null;
        }

        $step = RoutingSlipStep::query()->where('uuid', $uuid)->first();
        if (! $step) {
            throw ValidationException::withMessages([
                'routing_slip_step_uuid' => ['Routing step not found.'],
            ]);
        }

        return $step;
    }
}
