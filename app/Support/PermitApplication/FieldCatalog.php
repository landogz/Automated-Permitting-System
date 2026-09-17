<?php

declare(strict_types=1);

namespace App\Support\PermitApplication;

/**
 * Canonical permit application field catalog for QMS-36 / QMS-37 schemas.
 */
final class FieldCatalog
{
    /**
     * @return array{sections: list<array{title: string, fields: list<array<string, mixed>>}>}
     */
    public static function qms36Schema(): array
    {
        return [
            'sections' => [
                [
                    'title' => 'Owner / Applicant',
                    'fields' => [
                        ['name' => 'owner_name', 'label' => 'Owner full name', 'type' => 'text', 'required' => true],
                        ['name' => 'owner_address', 'label' => 'Owner address', 'type' => 'location', 'required' => true],
                        ['name' => 'owner_contact', 'label' => 'Owner contact number', 'type' => 'tel', 'required' => true],
                        ['name' => 'owner_email', 'label' => 'Owner email', 'type' => 'email', 'required' => false],
                        ['name' => 'owner_tin', 'label' => 'Owner TIN', 'type' => 'text', 'required' => false],
                        ['name' => 'applicant_name', 'label' => 'Applicant name (if not owner)', 'type' => 'text', 'required' => false],
                        ['name' => 'applicant_relation', 'label' => 'Relation to owner', 'type' => 'text', 'required' => false],
                        ['name' => 'applicant_contact', 'label' => 'Applicant contact', 'type' => 'tel', 'required' => false],
                    ],
                ],
                [
                    'title' => 'Property / Location',
                    'fields' => [
                        ['name' => 'barangay', 'label' => 'Barangay', 'type' => 'text', 'required' => true],
                        ['name' => 'street_address', 'label' => 'Street / site address', 'type' => 'location', 'required' => true],
                        ['name' => 'tct_number', 'label' => 'TCT / OCT number', 'type' => 'text', 'required' => false],
                        ['name' => 'tax_declaration_no', 'label' => 'Tax declaration number', 'type' => 'text', 'required' => true],
                        ['name' => 'lot_number', 'label' => 'Lot number', 'type' => 'text', 'required' => false],
                        ['name' => 'block_number', 'label' => 'Block number', 'type' => 'text', 'required' => false],
                        ['name' => 'survey_number', 'label' => 'Survey / PSD number', 'type' => 'text', 'required' => false],
                        ['name' => 'pin', 'label' => 'Property index number (PIN)', 'type' => 'text', 'required' => false],
                    ],
                ],
                [
                    'title' => 'Project / Building data',
                    'fields' => [
                        ['name' => 'scope_of_work', 'label' => 'Scope of work', 'type' => 'select', 'required' => true, 'options' => [
                            'new_construction', 'addition', 'alteration', 'renovation', 'repair', 'demolition', 'occupancy',
                        ]],
                        ['name' => 'occupancy', 'label' => 'Occupancy group', 'type' => 'select', 'required' => true, 'options' => [
                            'Residential', 'Commercial', 'Institutional', 'Industrial', 'Educational', 'Assembly', 'Storage', 'Mixed-Use', 'Agricultural',
                        ]],
                        ['name' => 'character_of_occupancy', 'label' => 'Character of occupancy / use', 'type' => 'text', 'required' => false],
                        ['name' => 'lot_area', 'label' => 'Lot area (sqm)', 'type' => 'number', 'required' => true, 'step' => '0.01'],
                        ['name' => 'floor_area', 'label' => 'Total floor area (sqm)', 'type' => 'number', 'required' => true, 'step' => '0.01'],
                        ['name' => 'building_height', 'label' => 'Building height (m)', 'type' => 'number', 'required' => false, 'step' => '0.01'],
                        ['name' => 'number_of_storeys', 'label' => 'Number of storeys', 'type' => 'number', 'required' => true, 'step' => '1'],
                        ['name' => 'number_of_units', 'label' => 'Number of units / doors', 'type' => 'number', 'required' => false, 'step' => '1'],
                        ['name' => 'estimated_cost', 'label' => 'Estimated project cost (PHP)', 'type' => 'number', 'required' => true, 'step' => '0.01'],
                        ['name' => 'construction_start', 'label' => 'Target construction start', 'type' => 'date', 'required' => false],
                        ['name' => 'construction_end', 'label' => 'Target completion', 'type' => 'date', 'required' => false],
                        ['name' => 'existing_structures', 'label' => 'Existing structures on site', 'type' => 'textarea', 'required' => false],
                    ],
                ],
                [
                    'title' => 'Design professionals',
                    'fields' => [
                        ['name' => 'architect_name', 'label' => 'Architect name', 'type' => 'text', 'required' => false],
                        ['name' => 'architect_prc', 'label' => 'Architect PRC number', 'type' => 'text', 'required' => false],
                        ['name' => 'engineer_name', 'label' => 'Civil / structural engineer', 'type' => 'text', 'required' => true],
                        ['name' => 'engineer_prc', 'label' => 'Civil engineer PRC number', 'type' => 'text', 'required' => true],
                        ['name' => 'electrical_engineer_name', 'label' => 'Professional electrical engineer', 'type' => 'text', 'required' => false],
                        ['name' => 'electrical_prc', 'label' => 'Electrical PRC number', 'type' => 'text', 'required' => false],
                        ['name' => 'sanitary_engineer_name', 'label' => 'Sanitary engineer / master plumber', 'type' => 'text', 'required' => false],
                        ['name' => 'sanitary_prc', 'label' => 'Sanitary PRC / license', 'type' => 'text', 'required' => false],
                        ['name' => 'mechanical_engineer_name', 'label' => 'Mechanical engineer', 'type' => 'text', 'required' => false],
                        ['name' => 'mechanical_prc', 'label' => 'Mechanical PRC number', 'type' => 'text', 'required' => false],
                    ],
                ],
                [
                    'title' => 'Additional remarks',
                    'fields' => [
                        ['name' => 'right_of_way', 'label' => 'Right-of-way / access notes', 'type' => 'textarea', 'required' => false],
                        ['name' => 'notes', 'label' => 'Notes for OCBO', 'type' => 'textarea', 'required' => false],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{sections: list<array{title: string, fields: list<array<string, mixed>>}>}
     */
    public static function qms37Schema(): array
    {
        return [
            'sections' => [
                [
                    'title' => 'Supporting professional affidavit',
                    'fields' => [
                        ['name' => 'engineer_name', 'label' => 'Design professional name', 'type' => 'text', 'required' => true],
                        ['name' => 'prc_number', 'label' => 'PRC / license number', 'type' => 'text', 'required' => true],
                        ['name' => 'profession', 'label' => 'Profession', 'type' => 'select', 'required' => true, 'options' => [
                            'Architect', 'Civil Engineer', 'Electrical Engineer', 'Mechanical Engineer', 'Sanitary Engineer', 'Master Plumber',
                        ]],
                        ['name' => 'ptr_number', 'label' => 'PTR number', 'type' => 'text', 'required' => false],
                        ['name' => 'ptr_date', 'label' => 'PTR date', 'type' => 'date', 'required' => false],
                        ['name' => 'ptr_place', 'label' => 'PTR place issued', 'type' => 'text', 'required' => false],
                        ['name' => 'affidavit_date', 'label' => 'Affidavit date', 'type' => 'date', 'required' => false],
                        ['name' => 'affidavit_notes', 'label' => 'Affidavit / undertaking notes', 'type' => 'textarea', 'required' => false],
                    ],
                ],
            ],
        ];
    }

    /**
     * Named starter templates for the admin form builder.
     *
     * @return list<array{code: string, title: string, schema: array{sections: list<array{title: string, fields: list<array<string, mixed>>}>}, required_attachments: list<string>}>
     */
    public static function templates(): array
    {
        return [
            [
                'code' => 'QMS-36',
                'title' => 'Unified Application Form (QMS-36)',
                'schema' => self::qms36Schema(),
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
                'schema' => self::qms37Schema(),
                'required_attachments' => [
                    'notarized_affidavit',
                    'prc_id',
                    'ptr',
                ],
            ],
        ];
    }

    /**
     * Flatten sectioned schema into field defs.
     *
     * @param  array<string, mixed>  $schema
     * @return list<array<string, mixed>>
     */
    public static function flattenFields(array $schema): array
    {
        if (isset($schema['sections']) && is_array($schema['sections'])) {
            $fields = [];
            foreach ($schema['sections'] as $section) {
                if (! is_array($section) || ! isset($section['fields']) || ! is_array($section['fields'])) {
                    continue;
                }
                foreach ($section['fields'] as $field) {
                    if (is_array($field) && ! empty($field['name'])) {
                        $fields[] = $field;
                    }
                }
            }

            return $fields;
        }

        if (isset($schema['fields']) && is_array($schema['fields'])) {
            return array_values(array_filter(
                $schema['fields'],
                static fn ($field): bool => is_array($field) && ! empty($field['name'])
            ));
        }

        return [];
    }

    /**
     * Validation rules for known payload keys (nullable so drafts stay flexible).
     *
     * @return array<string, list<string>>
     */
    public static function payloadValidationRules(): array
    {
        $rules = ['payload' => ['nullable', 'array']];

        foreach ([...self::flattenFields(self::qms36Schema()), ...self::flattenFields(self::qms37Schema())] as $field) {
            $name = (string) $field['name'];
            $type = (string) ($field['type'] ?? 'text');
            $key = 'payload.'.$name;

            $rules[$key] = match ($type) {
                'number' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
                'email' => ['nullable', 'email', 'max:255'],
                'tel' => ['nullable', 'string', 'max:40'],
                'date' => ['nullable', 'date'],
                'textarea' => ['nullable', 'string', 'max:5000'],
                'select' => ['nullable', 'string', 'max:120'],
                'location' => ['nullable', 'string', 'max:500'],
                default => ['nullable', 'string', 'max:500'],
            };

            if ($type === 'location') {
                $rules['payload.'.$name.'_lat'] = ['nullable', 'numeric', 'between:-90,90'];
                $rules['payload.'.$name.'_lng'] = ['nullable', 'numeric', 'between:-180,180'];
            }
        }

        return $rules;
    }
}
