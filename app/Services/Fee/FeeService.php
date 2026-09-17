<?php

declare(strict_types=1);

namespace App\Services\Fee;

use App\Enums\FeeAgency;
use App\Enums\OrderOfPaymentStatus;
use App\Models\FeeRule;
use App\Models\NumberingSeries;
use App\Models\OrderOfPayment;
use App\Models\PermitApplication;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Integrations\Bfp\BfpFeeAdapter;
use App\Services\Integrations\Cto\CtoPaymentAdapter;
use App\Services\Integrations\Dpwh\DpwhFeeAdapter;
use App\Services\Notification\WorkflowNotifier;
use App\Services\Operations\OperationsWorkflow;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class FeeService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly CtoPaymentAdapter $cto,
        private readonly BfpFeeAdapter $bfp,
        private readonly DpwhFeeAdapter $dpwh,
        private readonly OperationsWorkflow $operations,
        private readonly WorkflowNotifier $notifier,
    ) {
    }

    public function listRules(string $search = '', int $perPage = 25): LengthAwarePaginator
    {
        return FeeRule::query()
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
     * @return array{active: int, inactive: int, lgu: int, bfp: int, dpwh: int, cto: int, total: int}
     */
    public function feeRuleSummary(): array
    {
        $byAgency = FeeRule::query()
            ->selectRaw('agency, COUNT(*) as aggregate')
            ->groupBy('agency')
            ->pluck('aggregate', 'agency');

        return [
            'active' => FeeRule::query()->where('is_active', true)->count(),
            'inactive' => FeeRule::query()->where('is_active', false)->count(),
            'lgu' => (int) ($byAgency[FeeAgency::Lgu->value] ?? 0),
            'bfp' => (int) ($byAgency[FeeAgency::Bfp->value] ?? 0),
            'dpwh' => (int) ($byAgency[FeeAgency::Dpwh->value] ?? 0),
            'cto' => (int) ($byAgency[FeeAgency::Cto->value] ?? 0),
            'total' => FeeRule::query()->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createRule(array $data): FeeRule
    {
        $rule = FeeRule::query()->create($data);

        $this->audit->log('fee_rule.created', [
            'rule_id' => $rule->uuid,
            'code' => $rule->code,
            'agency' => $rule->agency?->value ?? $rule->agency,
        ]);

        return $rule;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateRule(FeeRule $rule, array $data): FeeRule
    {
        $rule->fill($data);
        $rule->save();

        $this->audit->log('fee_rule.updated', [
            'rule_id' => $rule->uuid,
            'code' => $rule->code,
            'agency' => $rule->agency?->value ?? $rule->agency,
        ]);

        return $rule->refresh();
    }

    public function deleteRule(FeeRule $rule): void
    {
        $uuid = $rule->uuid;
        $code = $rule->code;
        $rule->delete();

        $this->audit->log('fee_rule.deleted', [
            'rule_id' => $uuid,
            'code' => $code,
        ]);
    }

    public function listOrders(string $search = '', ?string $status = null, int $perPage = 25, ?string $bucket = null): LengthAwarePaginator
    {
        return OrderOfPayment::query()
            ->with([
                'application:id,uuid,application_no,project_title,project_location,status,classification,payload',
                'lines',
                'assessedByUser:id,uuid,name',
            ])
            ->when($bucket === 'active', fn ($q) => $q->where('status', OrderOfPaymentStatus::Issued->value))
            ->when($bucket === 'completed', function ($q): void {
                $q->whereIn('status', [
                    OrderOfPaymentStatus::PaidStub->value,
                    OrderOfPaymentStatus::Cancelled->value,
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
                    $inner->where('oop_no', 'like', $like)
                        ->orWhere('cto_stub_reference', 'like', $like)
                        ->orWhere('payment_reference', 'like', $like)
                        ->orWhereHas('application', function ($app) use ($like): void {
                            $app->where('application_no', 'like', $like)
                                ->orWhere('project_title', 'like', $like)
                                ->orWhere('project_location', 'like', $like);
                        });
                });
            })
            ->latest('id')
            ->paginate(min(max($perPage, 1), 100));
    }

    /**
     * Dashboard-style counts for the OoP operations page.
     *
     * @return array{issued: int, paid_stub: int, cancelled: int, total: int, issued_amount: float, paid_amount: float}
     */
    public function orderSummary(): array
    {
        $issuedAmount = (float) OrderOfPayment::query()
            ->where('status', OrderOfPaymentStatus::Issued->value)
            ->sum('total_amount');
        $paidAmount = (float) OrderOfPayment::query()
            ->where('status', OrderOfPaymentStatus::PaidStub->value)
            ->sum('total_amount');

        return [
            'issued' => OrderOfPayment::query()->where('status', OrderOfPaymentStatus::Issued->value)->count(),
            'paid_stub' => OrderOfPayment::query()->where('status', OrderOfPaymentStatus::PaidStub->value)->count(),
            'cancelled' => OrderOfPayment::query()->where('status', OrderOfPaymentStatus::Cancelled->value)->count(),
            'total' => OrderOfPayment::query()->count(),
            'issued_amount' => round($issuedAmount, 2),
            'paid_amount' => round($paidAmount, 2),
        ];
    }

    /**
     * Preview fee lines for an application without issuing an OoP.
     *
     * @return array{lines: list<array{agency: string, description: string, amount: float}>, total: float, lot_area: float|null, floor_area: float|null, classification: string|null}
     */
    public function previewForApplication(PermitApplication $application): array
    {
        $this->operations->assertCanGenerateOrder($application);

        $payload = is_array($application->payload) ? $application->payload : [];
        $lines = $this->computeLines($application);
        $total = round((float) collect($lines)->sum('amount'), 2);

        return [
            'lines' => array_map(static fn (array $line): array => [
                'agency' => (string) $line['agency'],
                'description' => (string) $line['description'],
                'amount' => (float) $line['amount'],
            ], $lines),
            'total' => $total,
            'lot_area' => isset($payload['lot_area']) && is_numeric($payload['lot_area']) ? (float) $payload['lot_area'] : null,
            'floor_area' => isset($payload['floor_area']) && is_numeric($payload['floor_area']) ? (float) $payload['floor_area'] : null,
            'classification' => $application->classification ? (string) $application->classification : null,
            'application' => [
                'uuid' => $application->uuid,
                'application_no' => $application->application_no,
                'project_title' => $application->project_title,
                'project_location' => $application->project_location,
                'latitude' => $application->latitude,
                'longitude' => $application->longitude,
                'status' => $application->status,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function generateOrder(PermitApplication $application, User $assessor, array $data = []): OrderOfPayment
    {
        $this->operations->assertCanGenerateOrder($application);

        $order = DB::transaction(function () use ($application, $assessor, $data): OrderOfPayment {
            $lines = $this->computeLines($application);
            if ($lines === []) {
                throw ValidationException::withMessages([
                    'fees' => ['No active fee rules matched this application.'],
                ]);
            }

            $total = collect($lines)->sum('amount');
            if (! empty($data['override_total'])) {
                if (empty($data['override_reason']) || strlen((string) $data['override_reason']) < 5) {
                    throw ValidationException::withMessages([
                        'override_reason' => ['Override reason is required (min 5 characters).'],
                    ]);
                }
                $total = (float) $data['override_total'];
            }

            $order = OrderOfPayment::query()->create([
                'permit_application_id' => $application->id,
                'oop_no' => $this->nextNumber('order_of_payment', 'G02-'.date('Y').'-'),
                'status' => OrderOfPaymentStatus::Issued->value,
                'total_amount' => $total,
                'assessed_by' => $assessor->id,
                'issued_at' => now(),
                'override_reason' => $data['override_reason'] ?? null,
            ]);

            foreach ($lines as $index => $line) {
                $stubRef = null;
                $agency = FeeAgency::from($line['agency']);
                if ($agency === FeeAgency::Bfp) {
                    $stubRef = $this->bfp->assessFee([
                        'application_no' => (string) $application->application_no,
                        'amount' => $line['amount'],
                    ])['reference'];
                } elseif ($agency === FeeAgency::Dpwh) {
                    $stubRef = $this->dpwh->assessFee([
                        'application_no' => (string) $application->application_no,
                        'amount' => $line['amount'],
                    ])['reference'];
                }

                $order->lines()->create([
                    'fee_rule_id' => $line['fee_rule_id'],
                    'agency' => $line['agency'],
                    'description' => $line['description'],
                    'amount' => $line['amount'],
                    'external_stub_reference' => $stubRef,
                    'line_order' => $index + 1,
                ]);
            }

            $cto = $this->cto->postOrderOfPayment([
                'oop_no' => $order->oop_no,
                'amount' => $order->total_amount,
                'application_no' => (string) $application->application_no,
            ]);
            $order->update(['cto_stub_reference' => $cto['reference']]);

            if ($application->status !== 'disapproved') {
                $application->update(['status' => 'for_payment']);
            }

            $this->audit->log('order_of_payment.issued', [
                'oop_id' => $order->uuid,
                'oop_no' => $order->oop_no,
                'application_id' => $application->uuid,
                'total' => (string) $order->total_amount,
                'cto_reference' => $cto['reference'],
            ]);

            return $order->load(['lines', 'application.user', 'assessedByUser']);
        });

        $this->notifier->orderIssued($order);

        return $order;
    }

    public function markPaidStub(OrderOfPayment $order, User $actor, ?string $paymentReference = null): OrderOfPayment
    {
        if ($order->status !== OrderOfPaymentStatus::Issued) {
            throw ValidationException::withMessages([
                'order' => ['Only issued orders can be marked paid (stub).'],
            ]);
        }

        $order->fill([
            'status' => OrderOfPaymentStatus::PaidStub->value,
            'paid_at' => now(),
            'payment_reference' => $paymentReference ?: ('PAY-STUB-'.strtoupper(substr(md5($order->oop_no), 0, 8))),
        ]);
        $order->save();

        $application = $order->application;
        if ($application && ! in_array((string) $application->status, ['disapproved', 'released'], true)) {
            $application->update(['status' => 'released']);
        }

        $this->audit->log('order_of_payment.paid_stub', [
            'oop_id' => $order->uuid,
            'oop_no' => $order->oop_no,
            'actor_id' => $actor->uuid,
            'application_id' => $application?->uuid,
            'next_status' => $application?->fresh()?->status ?? $application?->status,
        ]);

        $paid = $order->fresh(['lines', 'application.user', 'assessedByUser']) ?? $order;
        $this->notifier->orderPaid($paid);

        return $paid;
    }

    /**
     * @return list<array{fee_rule_id: int|null, agency: string, description: string, amount: float}>
     */
    public function computeLines(PermitApplication $application): array
    {
        $payload = is_array($application->payload) ? $application->payload : [];
        $rules = FeeRule::query()->where('is_active', true)->orderBy('priority')->get();
        $lines = [];

        foreach ($rules as $rule) {
            if (! $this->matches($rule->conditions ?? [], $payload, $application)) {
                continue;
            }

            $amount = $this->computeAmount($rule, $payload);
            $agency = $rule->agency instanceof FeeAgency ? $rule->agency->value : (string) $rule->agency;

            $lines[] = [
                'fee_rule_id' => $rule->id,
                'agency' => $agency,
                'description' => $rule->name,
                'amount' => $amount,
            ];
        }

        return $lines;
    }

    /**
     * @param  list<array<string, mixed>>|array<string, mixed>|null  $conditions
     * @param  array<string, mixed>  $payload
     */
    private function matches(?array $conditions, array $payload, PermitApplication $application): bool
    {
        if ($conditions === null || $conditions === []) {
            return true;
        }

        foreach ($conditions as $condition) {
            if (! is_array($condition)) {
                continue;
            }
            $field = (string) ($condition['field'] ?? '');
            $operator = (string) ($condition['operator'] ?? '=');
            $expected = $condition['value'] ?? null;
            $actual = $payload[$field] ?? null;

            if ($field === 'classification') {
                $actual = $application->classification;
            }

            $ok = match ($operator) {
                '>=' => is_numeric($actual) && is_numeric($expected) && (float) $actual >= (float) $expected,
                '>' => is_numeric($actual) && is_numeric($expected) && (float) $actual > (float) $expected,
                '<=' => is_numeric($actual) && is_numeric($expected) && (float) $actual <= (float) $expected,
                '<' => is_numeric($actual) && is_numeric($expected) && (float) $actual < (float) $expected,
                'contains' => is_string($actual) && is_string($expected) && str_contains(mb_strtolower($actual), mb_strtolower($expected)),
                default => (string) $actual === (string) $expected,
            };

            if (! $ok) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function computeAmount(FeeRule $rule, array $payload): float
    {
        $basis = (string) $rule->basis;
        if ($basis === 'area_rate') {
            $area = (float) ($payload['lot_area'] ?? $payload['floor_area'] ?? 0);
            $rate = (float) ($rule->rate ?? 0);

            return round(max($area, 0) * $rate, 2);
        }

        return round((float) $rule->amount, 2);
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
