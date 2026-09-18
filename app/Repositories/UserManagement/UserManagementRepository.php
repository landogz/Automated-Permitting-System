<?php

declare(strict_types=1);

namespace App\Repositories\UserManagement;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class UserManagementRepository
{
    /**
     * @param  array{search?: string, role?: string, is_active?: string, approval_status?: string}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $role = trim((string) ($filters['role'] ?? ''));
        $isActive = $filters['is_active'] ?? null;
        $approvalStatus = trim((string) ($filters['approval_status'] ?? ''));

        return User::query()
            ->with(['department', 'roles'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', function (Builder $query) use ($role): void {
                $query->role($role);
            })
            ->when($isActive === '1' || $isActive === '0', function (Builder $query) use ($isActive): void {
                $query->where('is_active', $isActive === '1');
            })
            ->when($approvalStatus !== '', function (Builder $query) use ($approvalStatus): void {
                $query->where('approval_status', $approvalStatus);
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findByUuid(string $uuid): ?User
    {
        return User::query()->where('uuid', $uuid)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->refresh();
    }

    public function countAdmins(): int
    {
        return User::query()->role('admin')->where('is_active', true)->count();
    }

    /**
     * Unfiltered directory counts for the Users & Roles KPI strip.
     *
     * @return array{total: int, active: int, staff: int, applicants: int, pending: int, inactive: int}
     */
    public function summary(): array
    {
        $total = User::query()->count();
        $active = User::query()->where('is_active', true)->count();
        $pending = User::query()->where('approval_status', 'pending')->count();
        $applicants = User::query()->role('applicant')->count();
        $staff = max(0, $total - $applicants);

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => max(0, $total - $active),
            'staff' => $staff,
            'applicants' => $applicants,
            'pending' => $pending,
        ];
    }
}
