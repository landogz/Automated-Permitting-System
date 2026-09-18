<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'departments.manage',
            'forms.manage',
            'audit.view',
            'applications.manage',
            'users.manage',
            'workflow.manage',
            'evaluations.manage',
            'inspections.manage',
            'fees.manage',
            'compliance.manage',
            'records.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $matrix = [
            'admin' => $permissions,
            'building_official' => [
                'applications.manage',
                'users.manage',
                'workflow.manage',
                'evaluations.manage',
                'inspections.manage',
                'fees.manage',
                'compliance.manage',
                'records.manage',
            ],
            'receiving' => [
                'applications.manage',
                'evaluations.manage',
            ],
            'evaluator' => [
                'applications.manage',
                'evaluations.manage',
            ],
            'inspector' => [
                'applications.manage',
                'inspections.manage',
            ],
            'assessor' => [
                'applications.manage',
                'fees.manage',
            ],
            'compliance' => [
                'applications.manage',
                'compliance.manage',
            ],
            'records' => [
                'applications.manage',
                'records.manage',
            ],
            'staff' => [
                'applications.manage',
                'evaluations.manage',
                'inspections.manage',
                'fees.manage',
                'compliance.manage',
                'records.manage',
            ],
            'applicant' => [],
        ];

        foreach ($matrix as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName);
            $role->syncPermissions($rolePermissions);
        }
    }
}
