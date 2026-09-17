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
                'audit.view',
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
                'audit.view',
                'evaluations.manage',
            ],
            'evaluator' => [
                'applications.manage',
                'audit.view',
                'evaluations.manage',
            ],
            'inspector' => [
                'applications.manage',
                'audit.view',
                'inspections.manage',
            ],
            'assessor' => [
                'applications.manage',
                'audit.view',
                'fees.manage',
            ],
            'compliance' => [
                'applications.manage',
                'audit.view',
                'compliance.manage',
            ],
            'records' => [
                'applications.manage',
                'audit.view',
                'records.manage',
            ],
            'staff' => [
                'applications.manage',
                'audit.view',
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
