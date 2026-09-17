<?php

declare(strict_types=1);

namespace App\Services\Routing;

use App\Enums\PermitClassification;
use App\Enums\RoutingStepStatus;
use App\Models\NumberingSeries;
use App\Models\PermitApplication;
use App\Models\RoutingSlip;
use App\Models\RoutingSlipStep;
use App\Models\RoutingTemplate;
use App\Models\RoutingTemplateStep;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RoutingService
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function listTemplates(string $search = '', int $perPage = 25): LengthAwarePaginator
    {
        return RoutingTemplate::query()
            ->with(['steps.department'])
            ->when($search !== '', function ($q) use ($search): void {
                $like = '%'.$search.'%';
                $q->where(function ($inner) use ($like): void {
                    $inner->where('code', 'like', $like)->orWhere('name', 'like', $like);
                });
            })
            ->orderBy('classification')
            ->orderBy('code')
            ->paginate(min(max($perPage, 1), 100));
    }

    /**
     * @return array{active: int, inactive: int, simple: int, complex: int, highly_technical: int, total: int}
     */
    public function templateSummary(): array
    {
        $byClass = RoutingTemplate::query()
            ->selectRaw('classification, COUNT(*) as aggregate')
            ->groupBy('classification')
            ->pluck('aggregate', 'classification');

        return [
            'active' => RoutingTemplate::query()->where('is_active', true)->count(),
            'inactive' => RoutingTemplate::query()->where('is_active', false)->count(),
            'simple' => (int) ($byClass[PermitClassification::Simple->value] ?? 0),
            'complex' => (int) ($byClass[PermitClassification::Complex->value] ?? 0),
            'highly_technical' => (int) ($byClass[PermitClassification::HighlyTechnical->value] ?? 0),
            'total' => RoutingTemplate::query()->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createTemplate(array $data): RoutingTemplate
    {
        return DB::transaction(function () use ($data): RoutingTemplate {
            $steps = $data['steps'] ?? [];
            unset($data['steps']);

            $template = RoutingTemplate::query()->create($data);
            $this->syncTemplateSteps($template, $steps);

            $this->audit->log('routing_template.created', [
                'template_id' => $template->uuid,
                'code' => $template->code,
            ]);

            return $template->load(['steps.department']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateTemplate(RoutingTemplate $template, array $data): RoutingTemplate
    {
        return DB::transaction(function () use ($template, $data): RoutingTemplate {
            $steps = $data['steps'] ?? null;
            unset($data['steps']);

            $template->fill($data);
            $template->save();

            if (is_array($steps)) {
                $template->steps()->delete();
                $this->syncTemplateSteps($template, $steps);
            }

            $this->audit->log('routing_template.updated', [
                'template_id' => $template->uuid,
                'code' => $template->code,
            ]);

            return $template->load(['steps.department']);
        });
    }

    public function deleteTemplate(RoutingTemplate $template): void
    {
        $uuid = $template->uuid;
        $code = $template->code;
        $template->delete();

        $this->audit->log('routing_template.deleted', [
            'template_id' => $uuid,
            'code' => $code,
        ]);
    }

    public function generateSlip(PermitApplication $application, User $actor): RoutingSlip
    {
        if ($application->classification === null || $application->classification === '') {
            throw ValidationException::withMessages([
                'classification' => ['Classify the application before generating a routing slip.'],
            ]);
        }

        $classification = PermitClassification::from((string) $application->classification);

        $existing = RoutingSlip::query()
            ->where('permit_application_id', $application->id)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'routing_slip' => ['An open routing slip already exists for this application.'],
            ]);
        }

        $template = RoutingTemplate::query()
            ->with('steps')
            ->where('classification', $classification->value)
            ->where('is_active', true)
            ->first();

        if (! $template || $template->steps->isEmpty()) {
            throw ValidationException::withMessages([
                'routing_template' => ['No active routing template with steps for this classification.'],
            ]);
        }

        return DB::transaction(function () use ($application, $actor, $template, $classification): RoutingSlip {
            $slip = RoutingSlip::query()->create([
                'permit_application_id' => $application->id,
                'routing_template_id' => $template->id,
                'slip_no' => $this->nextSlipNumber(),
                'classification' => $classification->value,
                'status' => 'open',
                'generated_by' => $actor->id,
                'generated_at' => now(),
            ]);

            foreach ($template->steps as $step) {
                RoutingSlipStep::query()->create([
                    'routing_slip_id' => $slip->id,
                    'department_id' => $step->department_id,
                    'step_order' => $step->step_order,
                    'label' => $step->label,
                    'status' => RoutingStepStatus::Pending->value,
                ]);
            }

            if ($application->status === 'submitted') {
                $application->update(['status' => 'under_evaluation']);
            }

            $this->audit->log('routing_slip.generated', [
                'slip_id' => $slip->uuid,
                'slip_no' => $slip->slip_no,
                'application_id' => $application->uuid,
                'classification' => $classification->value,
            ]);

            return $slip->load(['steps.department', 'application', 'generatedBy']);
        });
    }

    public function startStep(RoutingSlipStep $step, User $actor): RoutingSlipStep
    {
        if ($step->status !== RoutingStepStatus::Pending) {
            throw ValidationException::withMessages([
                'step' => ['Only pending steps can be started.'],
            ]);
        }

        $step->fill([
            'status' => RoutingStepStatus::InProgress->value,
            'assigned_user_id' => $actor->id,
            'started_at' => now(),
        ]);
        $step->save();

        $this->audit->log('routing_step.started', [
            'step_id' => $step->uuid,
            'slip_id' => $step->slip?->uuid,
        ]);

        return $step->fresh(['department', 'assignedUser']) ?? $step;
    }

    public function completeStep(RoutingSlipStep $step, User $actor, ?string $notes = null): RoutingSlipStep
    {
        if (! in_array($step->status, [RoutingStepStatus::Pending, RoutingStepStatus::InProgress], true)) {
            throw ValidationException::withMessages([
                'step' => ['This step cannot be completed.'],
            ]);
        }

        $step->fill([
            'status' => RoutingStepStatus::Completed->value,
            'assigned_user_id' => $step->assigned_user_id ?? $actor->id,
            'started_at' => $step->started_at ?? now(),
            'completed_at' => now(),
            'notes' => $notes,
        ]);
        $step->save();

        $slip = $step->slip;
        if ($slip && $slip->steps()->whereNotIn('status', [RoutingStepStatus::Completed->value, RoutingStepStatus::Skipped->value])->doesntExist()) {
            $slip->update(['status' => 'completed']);
        }

        $this->audit->log('routing_step.completed', [
            'step_id' => $step->uuid,
            'slip_id' => $slip?->uuid,
        ]);

        return $step->fresh(['department', 'assignedUser']) ?? $step;
    }

    /**
     * @param  list<array<string, mixed>>  $steps
     */
    private function syncTemplateSteps(RoutingTemplate $template, array $steps): void
    {
        foreach (array_values($steps) as $index => $step) {
            $departmentId = $step['department_id'] ?? null;
            if (! $departmentId && ! empty($step['department_uuid'])) {
                $departmentId = \App\Models\Department::query()
                    ->where('uuid', $step['department_uuid'])
                    ->value('id');
            }

            if (! $departmentId) {
                throw ValidationException::withMessages([
                    'steps' => ['Each routing step requires a valid department.'],
                ]);
            }

            RoutingTemplateStep::query()->create([
                'routing_template_id' => $template->id,
                'department_id' => $departmentId,
                'step_order' => (int) ($step['step_order'] ?? ($index + 1)),
                'label' => (string) ($step['label'] ?? 'Evaluation step'),
                'sla_hours' => (int) ($step['sla_hours'] ?? 24),
            ]);
        }
    }

    private function nextSlipNumber(): string
    {
        $series = NumberingSeries::query()->where('key', 'routing_slip')->lockForUpdate()->first();
        if (! $series) {
            NumberingSeries::query()->create([
                'key' => 'routing_slip',
                'prefix' => 'RS-'.date('Y').'-',
                'next_number' => 1,
                'pad_length' => 6,
            ]);
            $series = NumberingSeries::query()->where('key', 'routing_slip')->lockForUpdate()->firstOrFail();
        }

        $number = $series->prefix.str_pad((string) $series->next_number, $series->pad_length, '0', STR_PAD_LEFT);
        $series->update(['next_number' => $series->next_number + 1]);

        return $number;
    }
}
