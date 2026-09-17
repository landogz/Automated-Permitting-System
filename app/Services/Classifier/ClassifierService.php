<?php

declare(strict_types=1);

namespace App\Services\Classifier;

use App\Enums\PermitClassification;
use App\Models\ClassificationRule;
use App\Models\PermitApplication;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Notification\WorkflowNotifier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ClassifierService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly WorkflowNotifier $notifier,
    ) {
    }

    public function listRules(string $search = '', int $perPage = 25): LengthAwarePaginator
    {
        return ClassificationRule::query()
            ->when($search !== '', function ($q) use ($search): void {
                $like = '%'.$search.'%';
                $q->where(function ($inner) use ($like): void {
                    $inner->where('code', 'like', $like)->orWhere('name', 'like', $like);
                });
            })
            ->orderBy('priority')
            ->orderBy('code')
            ->paginate(min(max($perPage, 1), 100));
    }

    /**
     * @return array{active: int, inactive: int, simple: int, complex: int, highly_technical: int, total: int}
     */
    public function ruleSummary(): array
    {
        $byClass = ClassificationRule::query()
            ->selectRaw('classification, COUNT(*) as aggregate')
            ->groupBy('classification')
            ->pluck('aggregate', 'classification');

        return [
            'active' => ClassificationRule::query()->where('is_active', true)->count(),
            'inactive' => ClassificationRule::query()->where('is_active', false)->count(),
            'simple' => (int) ($byClass[PermitClassification::Simple->value] ?? 0),
            'complex' => (int) ($byClass[PermitClassification::Complex->value] ?? 0),
            'highly_technical' => (int) ($byClass[PermitClassification::HighlyTechnical->value] ?? 0),
            'total' => ClassificationRule::query()->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createRule(array $data): ClassificationRule
    {
        $rule = ClassificationRule::query()->create($data);

        $this->audit->log('classification_rule.created', [
            'rule_id' => $rule->uuid,
            'code' => $rule->code,
            'classification' => $rule->classification->value,
        ]);

        return $rule;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateRule(ClassificationRule $rule, array $data): ClassificationRule
    {
        $rule->fill($data);
        $rule->save();

        $this->audit->log('classification_rule.updated', [
            'rule_id' => $rule->uuid,
            'code' => $rule->code,
        ]);

        return $rule->refresh();
    }

    public function deleteRule(ClassificationRule $rule): void
    {
        $uuid = $rule->uuid;
        $code = $rule->code;
        $rule->delete();

        $this->audit->log('classification_rule.deleted', [
            'rule_id' => $uuid,
            'code' => $code,
        ]);
    }

    /**
     * Auto-classify using active rules ordered by priority (lowest first wins).
     *
     * @return array{classification: PermitClassification, rule: ?ClassificationRule}
     */
    public function suggest(PermitApplication $application): array
    {
        $payload = is_array($application->payload) ? $application->payload : [];
        $rules = ClassificationRule::query()
            ->where('is_active', true)
            ->orderBy('priority')
            ->get();

        foreach ($rules as $rule) {
            if ($this->matches($rule, $payload, $application)) {
                return ['classification' => $rule->classification, 'rule' => $rule];
            }
        }

        return ['classification' => PermitClassification::Simple, 'rule' => null];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function classify(PermitApplication $application, User $actor, array $data): PermitApplication
    {
        if (! in_array($application->status, ['submitted', 'under_evaluation'], true)) {
            throw ValidationException::withMessages([
                'application' => ['Only submitted applications can be classified.'],
            ]);
        }

        $classified = DB::transaction(function () use ($application, $actor, $data): PermitApplication {
            $manual = isset($data['classification'])
                ? PermitClassification::from((string) $data['classification'])
                : null;

            if ($manual) {
                $classification = $manual;
                $ruleCode = 'manual';
            } else {
                $suggested = $this->suggest($application);
                $classification = $suggested['classification'];
                $ruleCode = $suggested['rule']?->code;
            }

            $application->fill([
                'classification' => $classification->value,
                'classified_by_rule' => $ruleCode,
                'classified_by' => $actor->id,
                'classified_at' => now(),
                'status' => 'under_evaluation',
            ]);
            $application->save();

            $this->audit->log('permit_application.classified', [
                'application_id' => $application->uuid,
                'application_no' => $application->application_no,
                'classification' => $classification->value,
                'rule' => $ruleCode,
            ]);

            return $application->fresh(['user', 'formDefinition']) ?? $application;
        });

        $this->notifier->applicationClassified($classified);

        return $classified;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function matches(ClassificationRule $rule, array $payload, PermitApplication $application): bool
    {
        $conditions = $rule->conditions ?? [];
        if ($conditions === []) {
            return false;
        }

        foreach ($conditions as $condition) {
            $field = (string) ($condition['field'] ?? '');
            $operator = (string) ($condition['operator'] ?? '=');
            $value = $condition['value'] ?? null;

            $actual = match ($field) {
                'project_title' => $application->project_title,
                'project_location' => $application->project_location,
                default => data_get($payload, $field),
            };

            if (! $this->compare($actual, $operator, $value)) {
                return false;
            }
        }

        return true;
    }

    private function compare(mixed $actual, string $operator, mixed $expected): bool
    {
        return match ($operator) {
            '>=' => is_numeric($actual) && is_numeric($expected) && (float) $actual >= (float) $expected,
            '>' => is_numeric($actual) && is_numeric($expected) && (float) $actual > (float) $expected,
            '<=' => is_numeric($actual) && is_numeric($expected) && (float) $actual <= (float) $expected,
            '<' => is_numeric($actual) && is_numeric($expected) && (float) $actual < (float) $expected,
            'contains' => is_string($actual) && is_string($expected)
                && str_contains(mb_strtolower($actual), mb_strtolower($expected)),
            'in' => is_array($expected) && in_array($actual, $expected, true),
            default => $actual == $expected,
        };
    }
}
