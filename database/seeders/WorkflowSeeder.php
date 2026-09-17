<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PermitClassification;
use App\Models\ClassificationRule;
use App\Models\Department;
use App\Models\NumberingSeries;
use App\Models\RoutingTemplate;
use Illuminate\Database\Seeder;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        ClassificationRule::query()->firstOrCreate(
            ['code' => 'SIMPLE-DEFAULT'],
            [
                'name' => 'Simple residential under 200sqm',
                'classification' => PermitClassification::Simple->value,
                'priority' => 10,
                'conditions' => [
                    ['field' => 'lot_area', 'operator' => '<', 'value' => 200],
                    ['field' => 'occupancy', 'operator' => 'contains', 'value' => 'Residential'],
                ],
                'sla_hours' => 72,
                'is_active' => true,
            ]
        );

        ClassificationRule::query()->firstOrCreate(
            ['code' => 'COMPLEX-AREA'],
            [
                'name' => 'Complex by lot area',
                'classification' => PermitClassification::Complex->value,
                'priority' => 20,
                'conditions' => [
                    ['field' => 'lot_area', 'operator' => '>=', 'value' => 200],
                    ['field' => 'lot_area', 'operator' => '<', 'value' => 1000],
                ],
                'sla_hours' => 168,
                'is_active' => true,
            ]
        );

        ClassificationRule::query()->firstOrCreate(
            ['code' => 'HT-LARGE'],
            [
                'name' => 'Highly technical large projects',
                'classification' => PermitClassification::HighlyTechnical->value,
                'priority' => 30,
                'conditions' => [
                    ['field' => 'lot_area', 'operator' => '>=', 'value' => 1000],
                ],
                'sla_hours' => 336,
                'is_active' => true,
            ]
        );

        $ocbo = Department::query()->where('code', 'OCBO')->first();
        $eng = Department::query()->where('code', 'ENG')->first();

        if ($ocbo && $eng) {
            $this->seedTemplate('RT-SIMPLE', 'Simple route', PermitClassification::Simple, [
                ['department_uuid' => $ocbo->uuid, 'label' => 'Receiving completeness', 'sla_hours' => 24],
                ['department_uuid' => $eng->uuid, 'label' => 'Technical evaluation', 'sla_hours' => 48],
            ]);

            $this->seedTemplate('RT-COMPLEX', 'Complex route', PermitClassification::Complex, [
                ['department_uuid' => $ocbo->uuid, 'label' => 'Receiving completeness', 'sla_hours' => 24],
                ['department_uuid' => $eng->uuid, 'label' => 'Structural review', 'sla_hours' => 72],
                ['department_uuid' => $eng->uuid, 'label' => 'Electrical / sanitary review', 'sla_hours' => 72],
            ]);

            $this->seedTemplate('RT-HT', 'Highly technical route', PermitClassification::HighlyTechnical, [
                ['department_uuid' => $ocbo->uuid, 'label' => 'Receiving completeness', 'sla_hours' => 24],
                ['department_uuid' => $eng->uuid, 'label' => 'Multi-discipline evaluation', 'sla_hours' => 120],
                ['department_uuid' => $ocbo->uuid, 'label' => 'Building Official review', 'sla_hours' => 72],
            ]);
        }

        NumberingSeries::query()->firstOrCreate(
            ['key' => 'routing_slip'],
            [
                'prefix' => 'RS-'.date('Y').'-',
                'next_number' => 1,
                'pad_length' => 6,
            ]
        );
    }

    /**
     * @param  list<array{department_uuid: string, label: string, sla_hours: int}>  $steps
     */
    private function seedTemplate(string $code, string $name, PermitClassification $classification, array $steps): void
    {
        $template = RoutingTemplate::query()->firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'classification' => $classification->value,
                'is_active' => true,
            ]
        );

        if ($template->steps()->exists()) {
            return;
        }

        foreach ($steps as $index => $step) {
            $departmentId = Department::query()->where('uuid', $step['department_uuid'])->value('id');
            if (! $departmentId) {
                continue;
            }

            $template->steps()->create([
                'department_id' => $departmentId,
                'step_order' => $index + 1,
                'label' => $step['label'],
                'sla_hours' => $step['sla_hours'],
            ]);
        }
    }
}
