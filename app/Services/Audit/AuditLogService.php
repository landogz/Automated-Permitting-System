<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Repositories\AuditLogRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

final class AuditLogService
{
    public function __construct(private readonly AuditLogRepository $repository)
    {
    }

    /**
     * @param  array{
     *     search?: string,
     *     category?: string,
     *     actor?: string,
     *     severity?: string,
     *     date_from?: string,
     *     date_to?: string,
     *     range?: string,
     *     per_page?: int
     * }  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        [$from, $to] = $this->resolveDateBounds(
            (string) ($filters['range'] ?? ''),
            (string) ($filters['date_from'] ?? ''),
            (string) ($filters['date_to'] ?? ''),
        );

        return $this->repository->paginateFiltered(
            search: (string) ($filters['search'] ?? ''),
            category: (string) ($filters['category'] ?? ''),
            actor: (string) ($filters['actor'] ?? ''),
            severity: (string) ($filters['severity'] ?? ''),
            dateFrom: $from,
            dateTo: $to,
            perPage: (int) ($filters['per_page'] ?? 50),
        );
    }

    /**
     * @return array{
     *     events_24h: int,
     *     active_sessions: int,
     *     fee_overrides_24h: int,
     *     security_anomalies_24h: int
     * }
     */
    public function summaryStats(): array
    {
        $since = now()->subDay();

        $events24h = AuditLog::query()->where('created_at', '>=', $since)->count();

        $feeOverrides = AuditLog::query()
            ->where('created_at', '>=', $since)
            ->where(function ($q): void {
                $q->where('event', 'like', 'fee_rule.%')
                    ->orWhere('event', 'like', '%override%')
                    ->orWhere('event', 'order_of_payment.paid_stub');
            })
            ->count();

        $security = AuditLog::query()
            ->where('created_at', '>=', $since)
            ->where(function ($q): void {
                $q->where('event', 'like', '%failed%')
                    ->orWhere('event', 'auth.login_failed')
                    ->orWhere('event', 'like', '%denied%');
            })
            ->count();

        $activeSessions = PersonalAccessToken::query()
            ->where(function ($q): void {
                $q->where('last_used_at', '>=', now()->subHours(12))
                    ->orWhere(function ($inner): void {
                        $inner->whereNull('last_used_at')
                            ->where('created_at', '>=', now()->subHours(12));
                    });
            })
            ->count();

        return [
            'events_24h' => $events24h,
            'active_sessions' => $activeSessions,
            'fee_overrides_24h' => $feeOverrides,
            'security_anomalies_24h' => $security,
        ];
    }

    /**
     * @return array{categories: list<array{value: string, label: string}>, actors: list<array{value: string, label: string}>}
     */
    public function filterOptions(): array
    {
        $categories = [];
        foreach (AuditEventClassifier::categoryLabels() as $value => $label) {
            $categories[] = ['value' => $value, 'label' => $label];
        }

        $actors = AuditLog::query()
            ->whereNotNull('actor_name')
            ->where('actor_name', '!=', '')
            ->orderByDesc('id')
            ->limit(500)
            ->get(['user_id', 'actor_name'])
            ->unique(static fn (AuditLog $row): string => ($row->user_id ? 'user:'.$row->user_id : 'name:'.$row->actor_name))
            ->sortBy('actor_name')
            ->values()
            ->map(static function (AuditLog $row): array {
                $value = $row->user_id
                    ? 'user:'.$row->user_id
                    : 'name:'.$row->actor_name;

                return [
                    'value' => $value,
                    'label' => (string) $row->actor_name,
                ];
            })
            ->values()
            ->all();

        return [
            'categories' => $categories,
            'actors' => $actors,
        ];
    }

    /**
     * @return array{0: Carbon|null, 1: Carbon|null}
     */
    private function resolveDateBounds(string $range, string $dateFrom, string $dateTo): array
    {
        $now = now();

        return match ($range) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            '24h' => [$now->copy()->subDay(), $now],
            '7d' => [$now->copy()->subDays(7)->startOfDay(), $now],
            'custom' => [
                $dateFrom !== '' ? Carbon::parse($dateFrom)->startOfDay() : null,
                $dateTo !== '' ? Carbon::parse($dateTo)->endOfDay() : null,
            ],
            default => [null, null],
        };
    }
}
