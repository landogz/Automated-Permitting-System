<?php

declare(strict_types=1);

namespace App\Repositories\Registration;

use App\Enums\RegistrationApprovalStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class RegistrationRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createApplicant(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginateByStatus(
        ?string $status,
        string $search = '',
        int $perPage = 15,
    ): LengthAwarePaginator {
        $query = User::query()
            ->role('applicant')
            ->with(['reviewedBy:id,uuid,name,email'])
            ->latest('registered_at')
            ->latest('id');

        if ($status !== null && $status !== '' && $status !== 'all') {
            $query->where('approval_status', $status);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($builder) use ($like): void {
                $builder->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        return $query->paginate(min(max($perPage, 1), 100));
    }

    public function findApplicantByUuid(string $uuid): ?User
    {
        return User::query()
            ->role('applicant')
            ->where('uuid', $uuid)
            ->first();
    }

    /**
     * @return list<User>
     */
    public function adminRecipients(): array
    {
        return User::query()
            ->role('admin')
            ->where('is_active', true)
            ->where('approval_status', RegistrationApprovalStatus::Approved->value)
            ->get()
            ->all();
    }
}
