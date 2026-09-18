<?php

declare(strict_types=1);

namespace App\Support\Evaluation;

/**
 * Canonical OCBO evaluation / routing form schemas (QMS-61/62/63/64).
 */
final class EvaluationFormCatalog
{
    public const DOC_QMS_61 = 'qms-61';

    public const DOC_QMS_62 = 'qms-62';

    public const DOC_QMS_63 = 'qms-63';

    public const DOC_QMS_64 = 'qms-64';

    /**
     * @return list<string>
     */
    public static function documentCodes(): array
    {
        return [
            self::DOC_QMS_61,
            self::DOC_QMS_62,
            self::DOC_QMS_63,
            self::DOC_QMS_64,
        ];
    }

    /**
     * @return list<array{code: string, form_code: string, title: string, schema: array<string, mixed>}>
     */
    public static function templates(): array
    {
        return [
            [
                'code' => self::DOC_QMS_61,
                'form_code' => 'QMS-61',
                'title' => 'Routing Slip (QMS-61)',
                'schema' => self::qms61Schema(),
            ],
            [
                'code' => self::DOC_QMS_62,
                'form_code' => 'QMS-62',
                'title' => 'Routing Path Template (QMS-62)',
                'schema' => self::qms62Schema(),
            ],
            [
                'code' => self::DOC_QMS_63,
                'form_code' => 'QMS-63',
                'title' => 'Evaluation Sheet (QMS-63)',
                'schema' => self::qms63Schema(),
            ],
            [
                'code' => self::DOC_QMS_64,
                'form_code' => 'QMS-64',
                'title' => 'Technical Findings (QMS-64)',
                'schema' => self::qms64Schema(),
            ],
        ];
    }

    /**
     * @return array{sections: list<array{title: string, fields: list<array<string, mixed>>}>}
     */
    public static function qms61Schema(): array
    {
        return [
            'sections' => [
                [
                    'title' => 'Routing slip',
                    'fields' => [
                        ['name' => 'slip_no', 'label' => 'Slip number', 'type' => 'text', 'required' => true],
                        ['name' => 'classification', 'label' => 'Classification', 'type' => 'text', 'required' => true],
                        ['name' => 'route_offices', 'label' => 'Route offices', 'type' => 'textarea', 'required' => true],
                        ['name' => 'remarks', 'label' => 'Routing remarks', 'type' => 'textarea', 'required' => false],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{sections: list<array{title: string, fields: list<array<string, mixed>>}>}
     */
    public static function qms62Schema(): array
    {
        return [
            'sections' => [
                [
                    'title' => 'Routing path template',
                    'fields' => [
                        ['name' => 'template_code', 'label' => 'Template code', 'type' => 'text', 'required' => true],
                        ['name' => 'classification', 'label' => 'Target classification', 'type' => 'select', 'required' => true, 'options' => [
                            'simple', 'complex', 'highly_technical',
                        ]],
                        ['name' => 'steps', 'label' => 'Ordered department steps', 'type' => 'textarea', 'required' => true],
                        ['name' => 'sla_hours', 'label' => 'Default step SLA (hours)', 'type' => 'number', 'required' => false],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{sections: list<array{title: string, fields: list<array<string, mixed>>}>, checklist: list<array{code: string, label: string}>}
     */
    public static function qms63Schema(): array
    {
        return [
            'sections' => [
                [
                    'title' => 'Documentary evaluation',
                    'fields' => [
                        ['name' => 'completeness', 'label' => 'Completeness checklist', 'type' => 'checklist', 'required' => true],
                        ['name' => 'overall_remarks', 'label' => 'Overall remarks', 'type' => 'textarea', 'required' => false],
                    ],
                ],
            ],
            'checklist' => self::qms63Checklist(),
        ];
    }

    /**
     * @return array{sections: list<array{title: string, fields: list<array<string, mixed>>}>, checklist: list<array{code: string, label: string}>}
     */
    public static function qms64Schema(): array
    {
        return [
            'sections' => [
                [
                    'title' => 'Technical findings',
                    'fields' => [
                        ['name' => 'technical', 'label' => 'Technical checklist', 'type' => 'checklist', 'required' => true],
                        ['name' => 'discipline_remarks', 'label' => 'Discipline remarks', 'type' => 'textarea', 'required' => false],
                    ],
                ],
            ],
            'checklist' => self::qms64Checklist(),
        ];
    }

    /**
     * @return list<array{code: string, label: string, status: string, remarks: string}>
     */
    public static function qms63DefaultItems(): array
    {
        return array_map(
            static fn (array $item): array => [
                'code' => $item['code'],
                'label' => $item['label'],
                'status' => 'na',
                'remarks' => '',
            ],
            self::qms63Checklist(),
        );
    }

    /**
     * @return list<array{code: string, label: string, status: string, remarks: string}>
     */
    public static function qms64DefaultItems(): array
    {
        return array_map(
            static fn (array $item): array => [
                'code' => $item['code'],
                'label' => $item['label'],
                'status' => 'na',
                'remarks' => '',
            ],
            self::qms64Checklist(),
        );
    }

    /**
     * Normalize legacy flat findings into QMS-63/64 shape.
     *
     * @param  mixed  $findings
     * @return array{form_code: string, completeness: list<array<string, mixed>>, technical: list<array<string, mixed>>, overall_remarks: string, discipline_remarks: string}
     */
    public static function normalizeFindings(mixed $findings): array
    {
        if (is_array($findings) && (isset($findings['completeness']) || isset($findings['technical']))) {
            return [
                'form_code' => (string) ($findings['form_code'] ?? 'QMS-63'),
                'completeness' => self::normalizeItemList($findings['completeness'] ?? self::qms63DefaultItems()),
                'technical' => self::normalizeItemList($findings['technical'] ?? self::qms64DefaultItems()),
                'overall_remarks' => (string) ($findings['overall_remarks'] ?? ''),
                'discipline_remarks' => (string) ($findings['discipline_remarks'] ?? ''),
            ];
        }

        $legacy = [];
        if (is_array($findings)) {
            foreach ($findings as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $legacy[] = [
                    'code' => (string) ($row['code'] ?? 'ITEM_'.($index + 1)),
                    'label' => (string) ($row['label'] ?? $row['item'] ?? 'Item '.($index + 1)),
                    'status' => (string) ($row['status'] ?? 'na'),
                    'remarks' => (string) ($row['remarks'] ?? $row['notes'] ?? ''),
                ];
            }
        }

        $completeness = $legacy !== [] ? $legacy : self::qms63DefaultItems();

        return [
            'form_code' => 'QMS-63',
            'completeness' => $completeness,
            'technical' => self::qms64DefaultItems(),
            'overall_remarks' => is_array($findings) ? (string) ($findings['overall_remarks'] ?? '') : '',
            'discipline_remarks' => '',
        ];
    }

    /**
     * @param  mixed  $items
     * @return list<array{code: string, label: string, status: string, remarks: string}>
     */
    private static function normalizeItemList(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $out = [];
        foreach ($items as $index => $row) {
            if (! is_array($row)) {
                continue;
            }
            $out[] = [
                'code' => (string) ($row['code'] ?? 'ITEM_'.($index + 1)),
                'label' => (string) ($row['label'] ?? $row['item'] ?? 'Item '.($index + 1)),
                'status' => (string) ($row['status'] ?? 'na'),
                'remarks' => (string) ($row['remarks'] ?? $row['notes'] ?? ''),
            ];
        }

        return $out;
    }

    /**
     * @return list<array{code: string, label: string}>
     */
    private static function qms63Checklist(): array
    {
        return [
            ['code' => 'APP_FORM', 'label' => 'Unified application form complete (QMS-36)'],
            ['code' => 'OWNERSHIP', 'label' => 'Ownership / authority documents'],
            ['code' => 'LOT_PLAN', 'label' => 'Lot plan / vicinity map'],
            ['code' => 'TAX_DEC', 'label' => 'Tax declaration / real property docs'],
            ['code' => 'BARANGAY', 'label' => 'Barangay clearance'],
            ['code' => 'STRUCT_PLANS', 'label' => 'Structural plans submitted'],
            ['code' => 'ARCH_PLANS', 'label' => 'Architectural plans submitted'],
            ['code' => 'ELEC_PLANS', 'label' => 'Electrical plans submitted'],
            ['code' => 'PLUMB_PLANS', 'label' => 'Plumbing / sanitary plans submitted'],
            ['code' => 'BOM', 'label' => 'Bill of materials / cost estimate'],
            ['code' => 'PROF_DOCS', 'label' => 'Design professional credentials (PRC/PTR)'],
            ['code' => 'SIGNATURES', 'label' => 'Required signatures / notarization'],
        ];
    }

    /**
     * @return list<array{code: string, label: string}>
     */
    private static function qms64Checklist(): array
    {
        return [
            ['code' => 'ARCH_COMPLY', 'label' => 'Architectural compliance vs NBC / zoning'],
            ['code' => 'STRUCT_DESIGN', 'label' => 'Structural design adequacy'],
            ['code' => 'FOUNDATION', 'label' => 'Foundation / soil assumptions'],
            ['code' => 'ELEC_LOAD', 'label' => 'Electrical load schedule / service'],
            ['code' => 'ELEC_SAFETY', 'label' => 'Electrical safety / grounding'],
            ['code' => 'SANITARY', 'label' => 'Sanitary / plumbing design'],
            ['code' => 'MECHANICAL', 'label' => 'Mechanical systems (if any)'],
            ['code' => 'FIRE_SAFETY', 'label' => 'Fire safety / egress provisions'],
            ['code' => 'ACCESSIBILITY', 'label' => 'Accessibility / BP 344'],
            ['code' => 'SETBACKS', 'label' => 'Setbacks / building envelope'],
            ['code' => 'OCCUPANCY', 'label' => 'Occupancy / use classification'],
            ['code' => 'SPECIAL', 'label' => 'Special clearances / ancillary permits'],
        ];
    }
}
