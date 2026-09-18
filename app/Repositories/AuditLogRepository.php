<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\AuditLog;
use App\Services\Audit\AuditEventClassifier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

final class AuditLogRepository
{
    public function paginate(string $search = '', int $perPage = 20): LengthAwarePaginator
    {
        return $this->paginateFiltered(
            search: $search,
            category: '',
            actor: '',
            severity: '',
            dateFrom: null,
            dateTo: null,
            perPage: $perPage,
        );
    }

    public function paginateFiltered(
        string $search = '',
        string $category = '',
        string $actor = '',
        string $severity = '',
        ?Carbon $dateFrom = null,
        ?Carbon $dateTo = null,
        int $perPage = 50,
    ): LengthAwarePaginator {
        $perPage = min(max($perPage, 1), 100);

        return AuditLog::query()
            ->with(['user:id,uuid,name,email'])
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.$search.'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->where('event', 'like', $like)
                        ->orWhere('actor_name', 'like', $like)
                        ->orWhere('ip_address', 'like', $like)
                        ->orWhere('meta', 'like', $like);
                });
            })
            ->when($actor !== '', function ($query) use ($actor): void {
                if (str_starts_with($actor, 'user:')) {
                    $id = (int) substr($actor, 5);
                    $query->where('user_id', $id);
                } elseif (str_starts_with($actor, 'name:')) {
                    $query->where('actor_name', substr($actor, 5));
                }
            })
            ->when($dateFrom !== null, fn ($q) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo !== null, fn ($q) => $q->where('created_at', '<=', $dateTo))
            ->when($category !== '', function ($query) use ($category): void {
                $this->applyCategoryFilter($query, $category);
            })
            ->when($severity !== '', function ($query) use ($severity): void {
                $this->applySeverityFilter($query, $severity);
            })
            ->latest('id')
            ->paginate($perPage);
    }

    public function findByUuid(string $uuid): ?AuditLog
    {
        return AuditLog::query()->with('user:id,uuid,name,email')->where('uuid', $uuid)->first();
    }

    private function applyCategoryFilter($query, string $category): void
    {
        match ($category) {
            AuditEventClassifier::CATEGORY_AUTHENTICATION => $query->where('event', 'like', 'auth.%'),
            AuditEventClassifier::CATEGORY_REGISTRATION => $query->where('event', 'like', 'registration.%'),
            AuditEventClassifier::CATEGORY_COMPLIANCE => $query->where(function ($q): void {
                $q->where('event', 'like', 'compliance_%')
                    ->orWhere('event', 'like', '%compliance%');
            }),
            AuditEventClassifier::CATEGORY_RECORDS => $query->where(function ($q): void {
                $q->where('event', 'like', 'logbook_%')
                    ->orWhere('event', 'like', 'archive_%');
            }),
            AuditEventClassifier::CATEGORY_FEE_OVERRIDES => $query->where(function ($q): void {
                $q->where('event', 'like', 'fee_rule.%')
                    ->orWhere('event', 'like', 'order_of_payment.%')
                    ->orWhere('event', 'like', '%override%');
            }),
            AuditEventClassifier::CATEGORY_SYSTEM_CONFIG => $query->where(function ($q): void {
                $q->where('event', 'like', 'department.%')
                    ->orWhere('event', 'like', 'form_%')
                    ->orWhere('event', 'like', 'classification_rule.%')
                    ->orWhere('event', 'like', 'routing_template.%')
                    ->orWhere('event', 'like', 'notification_template.%')
                    ->orWhere('event', 'like', 'user.%');
            }),
            AuditEventClassifier::CATEGORY_PERMIT_ROUTING => $query->where(function ($q): void {
                $q->where('event', 'like', 'permit_application.%')
                    ->orWhere('event', 'like', 'evaluation.%')
                    ->orWhere('event', 'like', 'evaluation_time.%')
                    ->orWhere('event', 'like', 'classification%')
                    ->orWhere('event', 'like', 'routing_%')
                    ->orWhere('event', 'like', 'inspection.%');
            }),
            AuditEventClassifier::CATEGORY_OTHER => $query->where(function ($q): void {
                $q->where('event', 'not like', 'auth.%')
                    ->where('event', 'not like', 'registration.%')
                    ->where('event', 'not like', 'compliance_%')
                    ->where('event', 'not like', 'logbook_%')
                    ->where('event', 'not like', 'archive_%')
                    ->where('event', 'not like', 'fee_rule.%')
                    ->where('event', 'not like', 'order_of_payment.%')
                    ->where('event', 'not like', 'department.%')
                    ->where('event', 'not like', 'form_%')
                    ->where('event', 'not like', 'classification_rule.%')
                    ->where('event', 'not like', 'routing_template.%')
                    ->where('event', 'not like', 'notification_template.%')
                    ->where('event', 'not like', 'user.%')
                    ->where('event', 'not like', 'permit_application.%')
                    ->where('event', 'not like', 'evaluation.%')
                    ->where('event', 'not like', 'evaluation_time.%')
                    ->where('event', 'not like', 'inspection.%')
                    ->where('event', 'not like', 'routing_%');
            }),
            default => null,
        };
    }

    private function applySeverityFilter($query, string $severity): void
    {
        match ($severity) {
            'failure' => $query->where(function ($q): void {
                $q->where('event', 'like', '%failed%')
                    ->orWhere('event', 'like', '%failure%')
                    ->orWhere('event', 'like', '%denied%')
                    ->orWhere('event', 'auth.login_failed');
            }),
            'warning' => $query->where(function ($q): void {
                $q->where('event', 'like', '%declined%')
                    ->orWhere('event', 'like', '%override%')
                    ->orWhere('event', 'like', '%deleted%')
                    ->orWhere('event', 'like', '%rejected%');
            }),
            'success' => $query->where(function ($q): void {
                $q->where('event', 'not like', '%failed%')
                    ->where('event', 'not like', '%failure%')
                    ->where('event', 'not like', '%denied%')
                    ->where('event', '!=', 'auth.login_failed')
                    ->where('event', 'not like', '%declined%')
                    ->where('event', 'not like', '%override%')
                    ->where('event', 'not like', '%deleted%')
                    ->where('event', 'not like', '%rejected%');
            }),
            default => null,
        };
    }
}
