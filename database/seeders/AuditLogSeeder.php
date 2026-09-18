<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@csfp.local')->first();
        $applicant = User::query()->where('email', 'applicant@csfp.local')->first();
        $evaluator = User::query()->where('email', 'evaluator@csfp.local')->first();

        if (! $admin) {
            return;
        }

        $events = [
            [
                'event' => 'auth.login',
                'user_id' => $admin->id,
                'actor_name' => $admin->name,
                'meta' => [
                    'email' => $admin->email,
                    'device' => 'seeder',
                    'actor_roles' => ['admin'],
                    'actor_email' => $admin->email,
                ],
            ],
            [
                'event' => 'auth.login_failed',
                'user_id' => null,
                'actor_name' => 'intruder@example.com',
                'meta' => [
                    'attempted_email' => 'intruder@example.com',
                    'reason' => 'invalid_credentials',
                ],
            ],
            [
                'event' => 'department.created',
                'user_id' => $admin->id,
                'actor_name' => $admin->name,
                'meta' => [
                    'code' => 'ENG',
                    'name' => 'Engineering Evaluation',
                    'actor_roles' => ['admin'],
                ],
            ],
            [
                'event' => 'registration.approved',
                'user_id' => $admin->id,
                'actor_name' => $admin->name,
                'meta' => [
                    'email' => $applicant?->email,
                    'actor_roles' => ['admin'],
                ],
            ],
            [
                'event' => 'permit_application.submitted',
                'user_id' => $applicant?->id,
                'actor_name' => $applicant?->name,
                'meta' => [
                    'application_no' => 'APICS-2026-000003',
                    'project_title' => 'Warehouse Expansion',
                    'actor_roles' => ['applicant'],
                ],
            ],
            [
                'event' => 'fee_rule.updated',
                'user_id' => $admin->id,
                'actor_name' => $admin->name,
                'meta' => [
                    'code' => 'BLDG-BASE',
                    'old_values' => ['amount' => '1500.00', 'is_active' => true],
                    'new_values' => ['amount' => '1750.00', 'is_active' => true],
                    'actor_roles' => ['admin'],
                ],
            ],
            [
                'event' => 'evaluation.decided',
                'user_id' => $evaluator?->id ?? $admin->id,
                'actor_name' => $evaluator?->name ?? $admin->name,
                'meta' => [
                    'application_no' => 'APICS-2026-000002',
                    'decision' => 'forward_inspection',
                    'actor_roles' => ['evaluator'],
                ],
            ],
        ];

        foreach ($events as $row) {
            $exists = AuditLog::query()
                ->where('event', $row['event'])
                ->where('actor_name', $row['actor_name'])
                ->where('created_at', '>=', now()->subMinute())
                ->exists();

            if ($exists) {
                continue;
            }

            AuditLog::query()->create([
                'event' => $row['event'],
                'user_id' => $row['user_id'],
                'actor_name' => $row['actor_name'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
                'meta' => $row['meta'],
            ]);
        }
    }
}
