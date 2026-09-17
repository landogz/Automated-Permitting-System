<?php

declare(strict_types=1);

namespace App\Services\Department;

use App\Models\Department;
use App\Repositories\DepartmentRepository;
use App\Services\Audit\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DepartmentService
{
    public function __construct(
        private readonly DepartmentRepository $repository,
        private readonly AuditLogger $audit,
    ) {
    }

    public function list(string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($search, $perPage);
    }

    /**
     * @param  array{code: string, name: string, description?: string|null, is_active?: bool}  $data
     */
    public function create(array $data): Department
    {
        return DB::transaction(function () use ($data): Department {
            $department = $this->repository->create([
                'code' => strtoupper($data['code']),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $this->audit->log('department.created', [
                'department_id' => $department->uuid,
                'code' => $department->code,
                'name' => $department->name,
            ]);

            return $department;
        });
    }

    /**
     * @param  array{code?: string, name?: string, description?: string|null, is_active?: bool}  $data
     */
    public function update(Department $department, array $data): Department
    {
        return DB::transaction(function () use ($department, $data): Department {
            if (isset($data['code'])) {
                $data['code'] = strtoupper($data['code']);
            }

            $department = $this->repository->update($department, $data);

            $this->audit->log('department.updated', [
                'department_id' => $department->uuid,
                'code' => $department->code,
                'name' => $department->name,
            ]);

            return $department;
        });
    }

    public function delete(Department $department): void
    {
        DB::transaction(function () use ($department): void {
            $uuid = $department->uuid;
            $code = $department->code;
            $name = $department->name;

            $this->repository->delete($department);

            $this->audit->log('department.deleted', [
                'department_id' => $uuid,
                'code' => $code,
                'name' => $name,
            ]);
        });
    }

    /**
     * @param  list<array{code: string, name: string, description?: string|null, is_active?: bool|string|int}>  $rows
     * @return array{created: int, updated: int, errors: list<string>}
     */
    public function importRows(array $rows, bool $dryRun = true): array
    {
        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $index + 1;
            $code = strtoupper(trim((string) ($row['code'] ?? '')));
            $name = trim((string) ($row['name'] ?? ''));

            if ($code === '' || $name === '') {
                $errors[] = "Row {$line}: code and name are required.";

                continue;
            }

            $isActive = filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive === null) {
                $isActive = true;
            }

            $existing = $this->repository->findByCode($code);
            if ($dryRun) {
                if ($existing) {
                    $updated++;
                } else {
                    $created++;
                }

                continue;
            }

            if ($existing) {
                $this->update($existing, [
                    'name' => $name,
                    'description' => $row['description'] ?? $existing->description,
                    'is_active' => $isActive,
                ]);
                $updated++;
            } else {
                $this->create([
                    'code' => $code,
                    'name' => $name,
                    'description' => $row['description'] ?? null,
                    'is_active' => $isActive,
                ]);
                $created++;
            }
        }

        if (! $dryRun) {
            $this->audit->log('department.imported', [
                'created' => $created,
                'updated' => $updated,
                'error_count' => count($errors),
            ]);
        }

        if ($errors !== [] && ($created + $updated) === 0) {
            throw ValidationException::withMessages(['import' => $errors]);
        }

        return compact('created', 'updated', 'errors');
    }
}
