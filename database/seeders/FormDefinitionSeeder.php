<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FormDefinition;
use App\Support\Evaluation\EvaluationFormCatalog;
use App\Support\Inspection\InspectionFormCatalog;
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
                'schema' => EvaluationFormCatalog::qms61Schema(),
                'required_attachments' => [],
            ],
            [
                'code' => 'QMS-62',
                'title' => 'Routing Path Template (QMS-62)',
                'schema' => EvaluationFormCatalog::qms62Schema(),
                'required_attachments' => [],
            ],
            [
                'code' => 'QMS-63',
                'title' => 'Evaluation Sheet (QMS-63)',
                'schema' => EvaluationFormCatalog::qms63Schema(),
                'required_attachments' => [],
            ],
            [
                'code' => 'QMS-64',
                'title' => 'Technical Findings (QMS-64)',
                'schema' => EvaluationFormCatalog::qms64Schema(),
                'required_attachments' => [],
            ],
            [
                'code' => 'QMS-38',
                'title' => 'Joint Inspection Schedule (QMS-38)',
                'schema' => InspectionFormCatalog::qms38Schema(),
                'required_attachments' => [],
            ],
            [
                'code' => 'QMS-39',
                'title' => 'Joint Inspection Team Assignment (QMS-39)',
                'schema' => InspectionFormCatalog::qms39Schema(),
                'required_attachments' => [],
            ],
            [
                'code' => 'O-03',
                'title' => 'Individual Inspector Notes (O-03)',
                'schema' => InspectionFormCatalog::o03Schema(),
                'required_attachments' => [],
            ],
            [
                'code' => 'QMS-65',
                'title' => 'Inspection Compliance Sheet (QMS-65)',
                'schema' => InspectionFormCatalog::qms65Schema(),
                'required_attachments' => [],
            ],
            [
                'code' => '77-006-E',
                'title' => 'Final Electrical Inspection (DPWH 77-006-E)',
                'schema' => InspectionFormCatalog::dpwh77006eSchema(),
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
