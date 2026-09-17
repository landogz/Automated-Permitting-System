<?php

declare(strict_types=1);

namespace App\Services\UserManagement;

use App\Enums\RegistrationApprovalStatus;
use App\Models\Department;
use App\Models\User;
use App\Repositories\UserManagement\UserManagementRepository;
use App\Services\Audit\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

final class UserManagementService
{
    /** @var list<string> */
    public const STAFF_ROLES = [
        'admin',
        'building_official',
        'receiving',
        'evaluator',
        'inspector',
        'assessor',
        'compliance',
        'records',
        'staff',
    ];

    public function __construct(
        private readonly UserManagementRepository $repository,
        private readonly AuditLogger $audit,
    ) {
    }

    /**
     * @param  array{search?: string, role?: string, is_active?: string, approval_status?: string}  $filters
     */
    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * @return array{roles: list<string>, staff_roles: list<string>, departments: list<array{uuid: string, code: string, name: string}>}
     */
    public function formMeta(): array
    {
        $roles = Role::query()->orderBy('name')->pluck('name')->map(fn ($name) => (string) $name)->values()->all();

        $departments = Department::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['uuid', 'code', 'name'])
            ->map(fn (Department $department): array => [
                'uuid' => $department->uuid,
                'code' => $department->code,
                'name' => $department->name,
            ])
            ->values()
            ->all();

        return [
            'roles' => $roles,
            'staff_roles' => self::STAFF_ROLES,
            'departments' => $departments,
        ];
    }

    /**
     * @param  array{name: string, email: string, phone?: string|null, password: string, role: string, department_uuid?: string|null, is_active?: bool}  $data
     */
    public function create(array $data, User $actor): User
    {
        $this->assertAssignableStaffRole($data['role']);

        return DB::transaction(function () use ($data, $actor): User {
            $user = $this->repository->create([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'is_active' => $data['is_active'] ?? true,
                'approval_status' => RegistrationApprovalStatus::Approved,
                'registered_at' => now(),
                'approved_at' => now(),
                'reviewed_by' => $actor->id,
                'department_id' => $this->resolveDepartmentId($data['department_uuid'] ?? null),
            ]);

            $user->syncRoles([$data['role']]);

            $this->audit->log('user.created', [
                'user_id' => $user->uuid,
                'email' => $user->email,
                'role' => $data['role'],
            ]);

            return $user->load(['department', 'roles']);
        });
    }

    /**
     * @param  array{name?: string, email?: string, phone?: string|null, password?: string|null, role?: string, department_uuid?: string|null, is_active?: bool, approval_status?: string}  $data
     */
    public function update(User $user, array $data, User $actor): User
    {
        return DB::transaction(function () use ($user, $data, $actor): User {
            if (array_key_exists('role', $data) && $data['role'] !== null) {
                $this->assertRoleChangeAllowed($user, (string) $data['role'], $actor);
            }

            if (array_key_exists('is_active', $data) && $data['is_active'] === false) {
                $this->assertCanDeactivate($user, $actor);
            }

            $payload = [];
            foreach (['name', 'phone', 'is_active'] as $field) {
                if (array_key_exists($field, $data)) {
                    $payload[$field] = $data[$field];
                }
            }

            if (isset($data['email'])) {
                $payload['email'] = strtolower((string) $data['email']);
            }

            if (! empty($data['password'])) {
                $payload['password'] = $data['password'];
            }

            if (array_key_exists('department_uuid', $data)) {
                $payload['department_id'] = $this->resolveDepartmentId($data['department_uuid']);
            }

            if (isset($data['approval_status'])) {
                $status = RegistrationApprovalStatus::from((string) $data['approval_status']);
                $payload['approval_status'] = $status;
                if ($status === RegistrationApprovalStatus::Approved) {
                    $payload['approved_at'] = $user->approved_at ?? now();
                    $payload['declined_at'] = null;
                }
                if ($status === RegistrationApprovalStatus::Declined) {
                    $payload['declined_at'] = now();
                }
            }

            $user = $this->repository->update($user, $payload);

            if (isset($data['role'])) {
                $user->syncRoles([(string) $data['role']]);
            }

            $this->audit->log('user.updated', [
                'user_id' => $user->uuid,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
                'is_active' => $user->is_active,
            ]);

            return $user->load(['department', 'roles']);
        });
    }

    public function deactivate(User $user, User $actor): User
    {
        return $this->update($user, ['is_active' => false], $actor);
    }

    private function resolveDepartmentId(?string $uuid): ?int
    {
        if ($uuid === null || $uuid === '') {
            return null;
        }

        $department = Department::query()->where('uuid', $uuid)->first();
        if (! $department) {
            throw ValidationException::withMessages([
                'department_uuid' => [__('Selected department was not found.')],
            ]);
        }

        return (int) $department->id;
    }

    private function assertAssignableStaffRole(string $role): void
    {
        if (! in_array($role, self::STAFF_ROLES, true)) {
            throw ValidationException::withMessages([
                'role' => [__('Staff accounts must use an office role (not applicant).')],
            ]);
        }

        if (! Role::query()->where('name', $role)->exists()) {
            throw ValidationException::withMessages([
                'role' => [__('Unknown role.')],
            ]);
        }
    }

    private function assertRoleChangeAllowed(User $user, string $newRole, User $actor): void
    {
        if (! Role::query()->where('name', $newRole)->exists()) {
            throw ValidationException::withMessages([
                'role' => [__('Unknown role.')],
            ]);
        }

        $wasAdmin = $user->hasRole('admin');
        $becomesAdmin = $newRole === 'admin';

        if ($wasAdmin && ! $becomesAdmin) {
            if ((int) $user->id === (int) $actor->id) {
                throw ValidationException::withMessages([
                    'role' => [__('You cannot remove your own admin role.')],
                ]);
            }

            if ($this->repository->countAdmins() <= 1) {
                throw ValidationException::withMessages([
                    'role' => [__('Cannot remove the last active administrator.')],
                ]);
            }
        }
    }

    private function assertCanDeactivate(User $user, User $actor): void
    {
        if ((int) $user->id === (int) $actor->id) {
            throw ValidationException::withMessages([
                'is_active' => [__('You cannot deactivate your own account.')],
            ]);
        }

        if ($user->hasRole('admin') && $this->repository->countAdmins() <= 1) {
            throw ValidationException::withMessages([
                'is_active' => [__('Cannot deactivate the last active administrator.')],
            ]);
        }
    }
}
