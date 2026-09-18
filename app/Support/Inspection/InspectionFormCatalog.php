<?php

declare(strict_types=1);

namespace App\Support\Inspection;

/**
 * Canonical OCBO inspection form schemas (QMS-38/39, O-03, QMS-65, DPWH 77-006-E).
 */
final class InspectionFormCatalog
{
    public const DOC_QMS_38 = 'qms-38';

    public const DOC_QMS_39 = 'qms-39';

    public const DOC_O_03 = 'o-03';

    public const DOC_QMS_65 = 'qms-65';

    public const DOC_DPWH_77_006_E = 'dpwh-77-006-e';

    /**
     * @return list<string>
     */
    public static function documentCodes(): array
    {
        return [
            self::DOC_QMS_38,
            self::DOC_QMS_39,
            self::DOC_O_03,
            self::DOC_QMS_65,
            self::DOC_DPWH_77_006_E,
        ];
    }

    /**
     * @return list<array{code: string, title: string, form_code: string, schema: array<string, mixed>}>
     */
    public static function templates(): array
    {
        return [
            [
                'code' => self::DOC_QMS_38,
                'form_code' => 'QMS-38',
                'title' => 'Joint Inspection Schedule (QMS-38)',
                'schema' => self::qms38Schema(),
            ],
            [
                'code' => self::DOC_QMS_39,
                'form_code' => 'QMS-39',
                'title' => 'Joint Inspection Team Assignment (QMS-39)',
                'schema' => self::qms39Schema(),
            ],
            [
                'code' => self::DOC_O_03,
                'form_code' => 'O-03',
                'title' => 'Individual Inspector Notes (O-03)',
                'schema' => self::o03Schema(),
            ],
            [
                'code' => self::DOC_QMS_65,
                'form_code' => 'QMS-65',
                'title' => 'Inspection Compliance Sheet (QMS-65)',
                'schema' => self::qms65Schema(),
            ],
            [
                'code' => self::DOC_DPWH_77_006_E,
                'form_code' => '77-006-E',
                'title' => 'Final Electrical Inspection (DPWH 77-006-E)',
                'schema' => self::dpwh77006eSchema(),
            ],
        ];
    }

    /**
     * @return array{sections: list<array{title: string, fields: list<array<string, mixed>>}>}
     */
    public static function qms38Schema(): array
    {
        return [
            'sections' => [
                [
                    'title' => 'Schedule',
                    'fields' => [
                        ['name' => 'inspection_date', 'label' => 'Inspection date / time', 'type' => 'datetime', 'required' => true],
                        ['name' => 'purpose', 'label' => 'Purpose of inspection', 'type' => 'textarea', 'required' => true],
                        ['name' => 'meeting_point', 'label' => 'Meeting point / assembly', 'type' => 'text', 'required' => false],
                        ['name' => 'disciplines', 'label' => 'Disciplines covered', 'type' => 'multiselect', 'required' => true, 'options' => self::disciplineOptions()],
                        ['name' => 'remarks', 'label' => 'Scheduling remarks', 'type' => 'textarea', 'required' => false],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{sections: list<array{title: string, fields: list<array<string, mixed>>}>}
     */
    public static function qms39Schema(): array
    {
        return [
            'sections' => [
                [
                    'title' => 'Inspection team',
                    'fields' => [
                        ['name' => 'team_inspectors', 'label' => 'Assigned inspectors', 'type' => 'team', 'required' => true],
                        ['name' => 'lead_inspector', 'label' => 'Lead inspector', 'type' => 'text', 'required' => true],
                        ['name' => 'coordination_notes', 'label' => 'Coordination notes', 'type' => 'textarea', 'required' => false],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{sections: list<array{title: string, fields: list<array<string, mixed>>}>}
     */
    public static function o03Schema(): array
    {
        return [
            'sections' => [
                [
                    'title' => 'Site observations',
                    'fields' => [
                        ['name' => 'weather', 'label' => 'Weather / site access', 'type' => 'text', 'required' => false],
                        ['name' => 'site_conditions', 'label' => 'Site conditions', 'type' => 'textarea', 'required' => false],
                        ['name' => 'findings', 'label' => 'Findings', 'type' => 'textarea', 'required' => true],
                        ['name' => 'observed_defects', 'label' => 'Observed defects / non-conformances', 'type' => 'textarea', 'required' => false],
                        ['name' => 'recommendations', 'label' => 'Recommendations', 'type' => 'textarea', 'required' => false],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{sections: list<array{title: string, fields: list<array<string, mixed>>}>, checklist: list<array{code: string, label: string}>}
     */
    public static function qms65Schema(): array
    {
        return [
            'sections' => [
                [
                    'title' => 'Compliance checklist',
                    'fields' => [
                        ['name' => 'items', 'label' => 'Checklist items', 'type' => 'checklist', 'required' => true],
                        ['name' => 'overall_remarks', 'label' => 'Overall remarks', 'type' => 'textarea', 'required' => false],
                    ],
                ],
            ],
            'checklist' => self::qms65Checklist(),
        ];
    }

    /**
     * @return array{sections: list<array{title: string, fields: list<array<string, mixed>>}>}
     */
    public static function dpwh77006eSchema(): array
    {
        return [
            'sections' => [
                [
                    'title' => 'Electrical inspection items',
                    'fields' => [
                        ['name' => 'service_entrance', 'label' => 'Service entrance', 'type' => 'select', 'required' => true, 'options' => self::statusOptions()],
                        ['name' => 'grounding', 'label' => 'Grounding / bonding', 'type' => 'select', 'required' => true, 'options' => self::statusOptions()],
                        ['name' => 'panel_boards', 'label' => 'Panel boards / overcurrent', 'type' => 'select', 'required' => true, 'options' => self::statusOptions()],
                        ['name' => 'wiring_methods', 'label' => 'Wiring methods / raceways', 'type' => 'select', 'required' => true, 'options' => self::statusOptions()],
                        ['name' => 'fixtures_devices', 'label' => 'Fixtures / devices', 'type' => 'select', 'required' => true, 'options' => self::statusOptions()],
                        ['name' => 'load_schedule', 'label' => 'Load schedule conformance', 'type' => 'select', 'required' => true, 'options' => self::statusOptions()],
                        ['name' => 'remarks', 'label' => 'Electrical remarks', 'type' => 'textarea', 'required' => false],
                        ['name' => 'result', 'label' => 'Electrical result', 'type' => 'select', 'required' => true, 'options' => [
                            'passed', 'failed', 'conditional', 'na',
                        ]],
                    ],
                ],
            ],
        ];
    }

    /**
     * Default QMS-65 checklist rows for capture UI / seed.
     *
     * @return list<array{code: string, label: string, status: string, remarks: string}>
     */
    public static function qms65DefaultItems(): array
    {
        return array_map(
            static fn (array $item): array => [
                'code' => $item['code'],
                'label' => $item['label'],
                'status' => 'na',
                'remarks' => '',
            ],
            self::qms65Checklist(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function blankElectricalForm(): array
    {
        return [
            'form_code' => '77-006-E',
            'service_entrance' => 'na',
            'grounding' => 'na',
            'panel_boards' => 'na',
            'wiring_methods' => 'na',
            'fixtures_devices' => 'na',
            'load_schedule' => 'na',
            'remarks' => '',
            'result' => 'na',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function blankInspectorNotes(): array
    {
        return [
            'form_code' => 'O-03',
            'weather' => '',
            'site_conditions' => '',
            'findings' => '',
            'observed_defects' => '',
            'recommendations' => '',
        ];
    }

    /**
     * Normalize legacy flat compliance rows into QMS-65 shape.
     *
     * @param  mixed  $sheet
     * @return array{form_code: string, items: list<array<string, mixed>>, overall_remarks: string}
     */
    public static function normalizeComplianceSheet(mixed $sheet): array
    {
        if (is_array($sheet) && isset($sheet['items']) && is_array($sheet['items'])) {
            return [
                'form_code' => (string) ($sheet['form_code'] ?? 'QMS-65'),
                'items' => array_values(array_filter(
                    $sheet['items'],
                    static fn ($row): bool => is_array($row),
                )),
                'overall_remarks' => (string) ($sheet['overall_remarks'] ?? ''),
            ];
        }

        $items = [];
        if (is_array($sheet)) {
            foreach ($sheet as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $label = (string) ($row['label'] ?? $row['item'] ?? 'Item '.($index + 1));
                $items[] = [
                    'code' => (string) ($row['code'] ?? 'ITEM_'.($index + 1)),
                    'label' => $label,
                    'status' => (string) ($row['status'] ?? 'na'),
                    'remarks' => (string) ($row['remarks'] ?? $row['notes'] ?? ''),
                ];
            }
        }

        if ($items === []) {
            $items = self::qms65DefaultItems();
        }

        return [
            'form_code' => 'QMS-65',
            'items' => $items,
            'overall_remarks' => is_array($sheet) ? (string) ($sheet['overall_remarks'] ?? '') : '',
        ];
    }

    /**
     * @param  mixed  $form
     * @return array<string, mixed>
     */
    public static function normalizeElectricalForm(mixed $form): array
    {
        $blank = self::blankElectricalForm();
        if (! is_array($form)) {
            return $blank;
        }

        return array_merge($blank, [
            'form_code' => '77-006-E',
            'service_entrance' => (string) ($form['service_entrance'] ?? $blank['service_entrance']),
            'grounding' => (string) ($form['grounding'] ?? $blank['grounding']),
            'panel_boards' => (string) ($form['panel_boards'] ?? $blank['panel_boards']),
            'wiring_methods' => (string) ($form['wiring_methods'] ?? $blank['wiring_methods']),
            'fixtures_devices' => (string) ($form['fixtures_devices'] ?? $blank['fixtures_devices']),
            'load_schedule' => (string) ($form['load_schedule'] ?? $blank['load_schedule']),
            'remarks' => (string) ($form['remarks'] ?? ''),
            'result' => (string) ($form['result'] ?? $form['status'] ?? $blank['result']),
        ]);
    }

    /**
     * @param  mixed  $notes
     * @return array<string, mixed>
     */
    public static function normalizeInspectorNotes(mixed $notes): array
    {
        $blank = self::blankInspectorNotes();
        if (! is_array($notes)) {
            return $blank;
        }

        return array_merge($blank, [
            'form_code' => 'O-03',
            'weather' => (string) ($notes['weather'] ?? ''),
            'site_conditions' => (string) ($notes['site_conditions'] ?? ''),
            'findings' => (string) ($notes['findings'] ?? ''),
            'observed_defects' => (string) ($notes['observed_defects'] ?? ''),
            'recommendations' => (string) ($notes['recommendations'] ?? ''),
        ]);
    }

    /**
     * @param  mixed  $team
     * @return list<array{name: string, role: string, discipline: string}>
     */
    public static function normalizeTeamInspectors(mixed $team): array
    {
        if (! is_array($team)) {
            return [];
        }

        $rows = [];
        foreach ($team as $member) {
            if (! is_array($member)) {
                continue;
            }
            $name = trim((string) ($member['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $rows[] = [
                'name' => $name,
                'role' => trim((string) ($member['role'] ?? 'Member')),
                'discipline' => trim((string) ($member['discipline'] ?? '')),
            ];
        }

        return $rows;
    }

    /**
     * Whether electrical form capture is required for this inspection type.
     */
    public static function requiresElectricalForm(?string $type): bool
    {
        $key = strtolower((string) $type);

        return $key === 'electrical'
            || $key === 'final'
            || str_contains($key, 'electrical');
    }

    /**
     * @return list<array{code: string, label: string}>
     */
    private static function qms65Checklist(): array
    {
        return [
            ['code' => 'SETBACK', 'label' => 'Setbacks per approved plans'],
            ['code' => 'FOUNDATION', 'label' => 'Foundation / footing conformance'],
            ['code' => 'STRUCTURAL', 'label' => 'Structural framing / members'],
            ['code' => 'ARCHITECTURAL', 'label' => 'Architectural layout / occupancy'],
            ['code' => 'FIREWALL', 'label' => 'Firewall / fire separation'],
            ['code' => 'MEANS_EGRESS', 'label' => 'Means of egress / exits'],
            ['code' => 'ELECTRICAL', 'label' => 'Electrical rough-in / installations'],
            ['code' => 'SANITARY', 'label' => 'Sanitary / plumbing installations'],
            ['code' => 'MECHANICAL', 'label' => 'Mechanical installations'],
            ['code' => 'FIRE_SAFETY', 'label' => 'Fire safety provisions'],
            ['code' => 'SITE_SAFETY', 'label' => 'Site safety / housekeeping'],
            ['code' => 'APPROVED_PLANS', 'label' => 'Works match approved plans'],
        ];
    }

    /**
     * @return list<string>
     */
    private static function disciplineOptions(): array
    {
        return [
            'structural',
            'architectural',
            'electrical',
            'sanitary',
            'mechanical',
            'fire_safety',
        ];
    }

    /**
     * @return list<string>
     */
    private static function statusOptions(): array
    {
        return ['ok', 'fail', 'na'];
    }
}
