<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['code' => 'OCBO', 'name' => 'Office of the City Building Official', 'description' => 'City of San Fernando, Pampanga'],
            ['code' => 'CICTO', 'name' => 'City Information and Communications Technology Office', 'description' => 'ICT support and systems'],
            ['code' => 'CTO', 'name' => 'City Treasurer\'s Office', 'description' => 'Payments and assessment coordination (stub)'],
            ['code' => 'BFP', 'name' => 'Bureau of Fire Protection', 'description' => 'Fire safety fee/clearance stub partner'],
            ['code' => 'ENG', 'name' => 'Engineering Evaluation', 'description' => 'Structural / electrical / sanitary evaluation desk'],
        ];

        foreach ($departments as $row) {
            Department::query()->firstOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
