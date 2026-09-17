<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class DepartmentRepository
{
    public function paginate(string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return Department::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('code')
            ->paginate($perPage);
    }

    public function allActive(): Collection
    {
        return Department::query()->where('is_active', true)->orderBy('code')->get();
    }

    public function findByUuid(string $uuid): ?Department
    {
        return Department::query()->where('uuid', $uuid)->first();
    }

    /**
     * @param  array{code: string, name: string, description?: string|null, is_active?: bool}  $data
     */
    public function create(array $data): Department
    {
        return Department::query()->create($data);
    }

    /**
     * @param  array{code?: string, name?: string, description?: string|null, is_active?: bool}  $data
     */
    public function update(Department $department, array $data): Department
    {
        $department->update($data);

        return $department->refresh();
    }

    public function delete(Department $department): void
    {
        $department->delete();
    }

    public function findByCode(string $code): ?Department
    {
        return Department::query()->where('code', $code)->first();
    }
}
