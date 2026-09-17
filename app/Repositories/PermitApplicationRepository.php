<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\PermitApplication;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PermitApplicationRepository
{
    public function paginateForUser(User $user, string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return PermitApplication::query()
            ->with(['formDefinition:id,uuid,code,title'])
            ->where('user_id', $user->id)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('application_no', 'like', "%{$search}%")
                        ->orWhere('project_title', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Staff lookup: search applications by number, title, location, UUID, or applicant.
     *
     * @param  list<string>|null  $statuses
     */
    public function paginateLookup(
        string $search = '',
        int $perPage = 20,
        ?array $statuses = null,
        bool $excludeWithOpenInspection = false,
    ): LengthAwarePaginator {
        $perPage = min(max($perPage, 1), 50);

        return PermitApplication::query()
            ->with(['user:id,uuid,name,email'])
            ->when($statuses !== null && $statuses !== [], fn ($q) => $q->whereIn('status', $statuses))
            ->when($excludeWithOpenInspection, function ($query): void {
                $query->whereDoesntHave('inspections', function ($insp): void {
                    $insp->whereIn('status', ['scheduled', 'in_progress']);
                });
            })
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.$search.'%';
                $query->where(function ($inner) use ($like, $search): void {
                    $inner->where('application_no', 'like', $like)
                        ->orWhere('project_title', 'like', $like)
                        ->orWhere('project_location', 'like', $like)
                        ->orWhere('uuid', 'like', $like)
                        ->orWhere('status', 'like', $like)
                        ->orWhereHas('user', function ($userQuery) use ($like): void {
                            $userQuery->where('name', 'like', $like)
                                ->orWhere('email', 'like', $like);
                        });

                    if (strlen($search) >= 8) {
                        $inner->orWhere('uuid', $search);
                    }
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findByUuidForUser(string $uuid, User $user): ?PermitApplication
    {
        return PermitApplication::query()
            ->with([
                'formDefinition',
                'documents',
                'inspections' => fn ($q) => $q->latest('completed_at'),
                'complianceNotices' => fn ($q) => $q
                    ->with([
                        'inspection',
                        'appeals' => fn ($appeals) => $appeals
                            ->with(['filedByUser:id,uuid,name', 'resolvedByUser:id,uuid,name'])
                            ->latest('id'),
                    ])
                    ->latest('id'),
            ])
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->first();
    }

    public function findByUuid(string $uuid): ?PermitApplication
    {
        return PermitApplication::query()
            ->with([
                'formDefinition',
                'documents',
                'user:id,uuid,name,email,phone',
                'inspections' => fn ($q) => $q->latest('completed_at'),
                'complianceNotices' => fn ($q) => $q
                    ->with([
                        'inspection',
                        'appeals' => fn ($appeals) => $appeals
                            ->with(['filedByUser:id,uuid,name', 'resolvedByUser:id,uuid,name'])
                            ->latest('id'),
                    ])
                    ->latest('id'),
            ])
            ->where('uuid', $uuid)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PermitApplication
    {
        return PermitApplication::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PermitApplication $application, array $data): PermitApplication
    {
        $application->update($data);

        return $application->refresh()->load(['formDefinition', 'documents']);
    }
}
