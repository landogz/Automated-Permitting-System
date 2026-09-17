<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class AuditLogRepository
{
    public function paginate(string $search = '', int $perPage = 20): LengthAwarePaginator
    {
        return AuditLog::query()
            ->with(['user:id,uuid,name,email'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('event', 'like', "%{$search}%")
                        ->orWhere('actor_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findByUuid(string $uuid): ?AuditLog
    {
        return AuditLog::query()->with('user:id,uuid,name,email')->where('uuid', $uuid)->first();
    }
}
