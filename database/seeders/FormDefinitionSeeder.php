<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FormDefinition;
use App\Support\PermitApplication\FieldCatalog;
use Illuminate\Database\Seeder;

class FormDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $forms = [
            [
                'code' => 'QMS-36',
                'title' => 'Unified Application Form (QMS-36)',
                'schema' => FieldCatalog::qms36Schema(),
                'required_attachments' => [
                    'tax_declaration',
                    'lot_plan',
                    'tct_or_deed',
                    'barangay_clearance',
                    'bill_of_materials',
                    'structural_plans',
                    'architectural_plans',
                    'electrical_plans',
                    'plumbing_plans',
                ],
            ],
            [
                'code' => 'QMS-37',
                'title' => 'Unified Supporting Form (QMS-37)',
                'schema' => FieldCatalog::qms37Schema(),
                'required_attachments' => [
                    'notarized_affidavit',
                    'prc_id',
                    'ptr',
                ],
            ],
            [
                'code' => 'QMS-61',
                'title' => 'Routing Slip (QMS-61)',
                'schema' => [
                    'fields' => [
                        ['name' => 'route_to', 'label' => 'Route To Department', 'type' => 'text', 'required' => true],
                        ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'required' => false],
                    ],
                ],
                'required_attachments' => [],
            ],
            [
                'code' => 'QMS-38',
                'title' => 'Joint Inspection Schedule (QMS-38)',
                'schema' => [
                    'fields' => [
                        ['name' => 'inspection_date', 'label' => 'Inspection Date', 'type' => 'date', 'required' => true],
                        ['name' => 'inspectors', 'label' => 'Inspectors', 'type' => 'text', 'required' => true],
                    ],
                ],
                'required_attachments' => [],
            ],
        ];

        foreach ($forms as $form) {
            FormDefinition::query()->updateOrCreate(
                ['code' => $form['code']],
                [
                    'title' => $form['title'],
                    'revision' => '02',
                    'effective_date' => now()->toDateString(),
                    'schema' => $form['schema'],
                    'required_attachments' => $form['required_attachments'],
                    'is_active' => true,
                ]
            );
        }
    }
}
