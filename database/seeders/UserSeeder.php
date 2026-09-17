<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RegistrationApprovalStatus;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $ocbo = Department::query()->where('code', 'OCBO')->first();
        $cicto = Department::query()->where('code', 'CICTO')->first();
        $eng = Department::query()->where('code', 'ENG')->first();
        $cto = Department::query()->where('code', 'CTO')->first();

        $users = [
            [
                'email' => 'admin@csfp.local',
                'name' => 'APICS Administrator',
                'password' => 'Admin@12345',
                'role' => 'admin',
                'phone' => '09000000001',
                'department_id' => $cicto?->id ?? $ocbo?->id,
            ],
            [
                'email' => 'official@csfp.local',
                'name' => 'City Building Official',
                'password' => 'Official@123',
                'role' => 'building_official',
                'phone' => '09000000002',
                'department_id' => $ocbo?->id,
            ],
            [
                'email' => 'receiving@csfp.local',
                'name' => 'Front Desk Receiving',
                'password' => 'Receive@123',
                'role' => 'receiving',
                'phone' => '09000000003',
                'department_id' => $ocbo?->id,
            ],
            [
                'email' => 'evaluator@csfp.local',
                'name' => 'Technical Evaluator',
                'password' => 'Evaluate@123',
                'role' => 'evaluator',
                'phone' => '09000000004',
                'department_id' => $eng?->id ?? $ocbo?->id,
            ],
            [
                'email' => 'inspector@csfp.local',
                'name' => 'Field Inspector',
                'password' => 'Inspect@123',
                'role' => 'inspector',
                'phone' => '09000000005',
                'department_id' => $ocbo?->id,
            ],
            [
                'email' => 'assessor@csfp.local',
                'name' => 'Fee Assessor / Cashier',
                'password' => 'Assess@1234',
                'role' => 'assessor',
                'phone' => '09000000006',
                'department_id' => $cto?->id ?? $ocbo?->id,
            ],
            [
                'email' => 'compliance@csfp.local',
                'name' => 'Compliance Officer',
                'password' => 'Comply@1234',
                'role' => 'compliance',
                'phone' => '09000000007',
                'department_id' => $ocbo?->id,
            ],
            [
                'email' => 'records@csfp.local',
                'name' => 'Records Archivist',
                'password' => 'Records@123',
                'role' => 'records',
                'phone' => '09000000008',
                'department_id' => $ocbo?->id,
            ],
            [
                'email' => 'applicant@csfp.local',
                'name' => 'Demo Applicant',
                'password' => 'Applicant@123',
                'role' => 'applicant',
                'phone' => '09171230001',
                'department_id' => null,
            ],
            [
                'email' => 'applicant2@csfp.local',
                'name' => 'Maria Santos (Applicant)',
                'password' => 'Applicant@123',
                'role' => 'applicant',
                'phone' => '09171230002',
                'department_id' => null,
            ],
            [
                'email' => 'pending@csfp.local',
                'name' => 'Pending Registrant',
                'password' => 'Pending@123',
                'role' => 'applicant',
                'phone' => '09171230003',
                'department_id' => null,
                'approval_status' => RegistrationApprovalStatus::Pending->value,
                'is_active' => false,
            ],
        ];

        foreach ($users as $row) {
            $status = $row['approval_status'] ?? RegistrationApprovalStatus::Approved->value;
            $isActive = $row['is_active'] ?? true;

            $user = User::query()->firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make($row['password']),
                    'phone' => $row['phone'],
                    'department_id' => $row['department_id'],
                    'is_active' => $isActive,
                    'approval_status' => $status,
                    'approved_at' => $status === RegistrationApprovalStatus::Approved->value ? now() : null,
                    'registered_at' => now()->subDays(3),
                ]
            );

            $user->forceFill([
                'name' => $row['name'],
                'password' => Hash::make($row['password']),
                'phone' => $row['phone'],
                'department_id' => $row['department_id'],
                'is_active' => $isActive,
                'approval_status' => $status,
                'approved_at' => $status === RegistrationApprovalStatus::Approved->value
                    ? ($user->approved_at ?? now())
                    : null,
                'registered_at' => $user->registered_at ?? now()->subDays(3),
            ])->save();

            $user->syncRoles([$row['role']]);
        }
    }
}
