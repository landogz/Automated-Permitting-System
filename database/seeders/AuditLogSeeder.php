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

        if (! $admin) {
            return;
        }

        $events = [
            [
                'event' => 'auth.login',
                'user_id' => $admin->id,
                'actor_name' => $admin->name,
                'meta' => ['email' => $admin->email, 'device' => 'seeder'],
            ],
            [
                'event' => 'department.created',
                'user_id' => $admin->id,
                'actor_name' => $admin->name,
                'meta' => ['code' => 'ENG', 'name' => 'Engineering Evaluation'],
            ],
            [
                'event' => 'registration.approved',
                'user_id' => $admin->id,
                'actor_name' => $admin->name,
                'meta' => ['email' => $applicant?->email],
            ],
            [
                'event' => 'application.submitted',
                'user_id' => $applicant?->id,
                'actor_name' => $applicant?->name,
                'meta' => ['project_title' => 'Warehouse Expansion'],
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
                'user_agent' => 'DatabaseSeeder',
                'meta' => $row['meta'],
            ]);
        }
    }
}
