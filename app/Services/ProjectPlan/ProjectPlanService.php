<?php

declare(strict_types=1);

namespace App\Services\ProjectPlan;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

/**
 * Reads the living APICS plan markdown as the single source of truth.
 * Displays delivery Phases 0–8 from section 6, with statuses driven by frontmatter todos.
 */
final class ProjectPlanService
{
    private const PLAN_RELATIVE_PATH = '.cursor/plans/apics_phase_i_plan_5d06a183.plan.md';

    /**
     * Map frontmatter todo ids → delivery phase number (0–8).
     *
     * @var array<string, int>
     */
    private const TODO_PHASE_MAP = [
        'plan-sync-rule' => 0,
        'docs-suite' => 1,
        'foundation' => 2,
        'forms-intake' => 3,
        'registration-approval' => 3,
        'classifier-eval' => 4,
        'inspection-fees-compliance' => 5,
        'records-notif' => 6,
        'qa-uat-deploy' => 7,
    ];

    /**
     * Fallback phase definitions when markdown sections cannot be parsed.
     *
     * @var array<int, array{title: string, weeks: string, items: list<string>}>
     */
    private const FALLBACK_PHASES = [
        0 => [
            'title' => 'Project setup',
            'weeks' => 'week 0–1',
            'items' => [
                'Env, CI, Sanctum, roles, audit base, design system, docs/ skeleton',
                'Admin shell + reusable Blade/JS components',
            ],
        ],
        1 => [
            'title' => 'Discovery & design',
            'weeks' => 'weeks 1–5',
            'items' => [
                'Stakeholder interviews and AS-IS vs TO-BE flows',
                'ERD, API map, UI mockups, threat model',
                'Docs: Charter, Process Flow, Phase Plan, Data Dictionary',
            ],
        ],
        2 => [
            'title' => 'Foundation',
            'weeks' => 'weeks 5–8',
            'items' => [
                'Auth (Sanctum), RBAC, audit, API envelope, DataTables patterns',
                'Admin CRUD scaffolding + import/export framework',
                'Seed departments, roles, sample barangays',
            ],
        ],
        3 => [
            'title' => 'Intake & Forms Engine',
            'weeks' => 'weeks 8–12',
            'items' => [
                'Dynamic forms + online application + uploads + tracking',
                'Citizens Portal adapter stub',
                'Registration approval + Admin forms/checklists/numbering',
            ],
        ],
        4 => [
            'title' => 'Classifier, Evaluation, Routing',
            'weeks' => 'weeks 12–15',
            'items' => [
                'Classification rules; routing slips; evaluation sheets; time tracking',
                'Admin: workflows, SLA, routing templates',
            ],
        ],
        5 => [
            'title' => 'Inspection, Fees, Compliance',
            'weeks' => 'weeks 15–19',
            'items' => [
                'Scheduling, notes, compliance sheets, electrical form',
                'Order of Payment + fee engine + CTO/BFP/DPWH stubs',
                'G-03/G-04 notices + appeals support',
            ],
        ],
        6 => [
            'title' => 'Records, Notifications, Polish',
            'weeks' => 'weeks 19–21',
            'items' => [
                'Logbooks G-01/O-02/E-series, G-05/G-06',
                'Notification engine; dashboards; print/PDF; responsive pass',
            ],
        ],
        7 => [
            'title' => 'QA, UAT, Deploy',
            'weeks' => 'weeks 21–27',
            'items' => [
                'Unit/feature/API tests; security review; UAT scripts',
                'Staging → training → production; Certificate of Acceptance',
                'Handover: source, docs, manuals, KT for CICTO',
            ],
        ],
        8 => [
            'title' => 'Warranty',
            'weeks' => '6 months post-acceptance',
            'items' => [
                'Bugfix SLA (24h response per TOR); patches; feedback loop',
            ],
        ],
    ];

    /**
     * @return array{
     *     project: string,
     *     overview: string|null,
     *     current_focus: string,
     *     updated_at: string,
     *     source: string,
     *     summary: array{total: int, completed: int, pending: int, in_progress: int, percent: int, todos_total: int, todos_completed: int},
     *     phases: list<array<string, mixed>>,
     *     roadmap: list<array{id: string, title: string, label: string}>,
     *     todos: list<array{id: string, label: string, status: string}>,
     *     changelog: list<array{date: string, completed: string, notes: string}>,
     *     next_steps: list<string>
     * }
     */
    public function summary(): array
    {
        $path = base_path(self::PLAN_RELATIVE_PATH);
        $contents = File::exists($path) ? File::get($path) : '';
        $todos = $this->parseTodosFromMarkdown($contents);
        $deliveryPhases = $this->parseDeliveryPhasesFromMarkdown($contents);
        $phases = $this->buildDeliveryPhases($todos, $deliveryPhases);
        $overview = $this->parseOverviewFromMarkdown($contents);
        $changelog = $this->parseChangelogFromMarkdown($contents);
        $nextSteps = $this->parseNextStepsFromMarkdown($contents);

        $total = count($phases);
        $completed = 0;
        $pending = 0;
        $inProgress = 0;

        foreach ($phases as $phase) {
            match ($phase['status']) {
                'completed' => $completed++,
                'in_progress' => $inProgress++,
                default => $pending++,
            };
        }

        $todosCompleted = count(array_filter(
            $todos,
            static fn (array $todo): bool => $todo['status'] === 'completed',
        ));

        $currentFocus = $this->resolveCurrentFocus($todos, $phases);
        $updatedAt = File::exists($path)
            ? Carbon::createFromTimestamp(File::lastModified($path))->toDateString()
            : now()->toDateString();

        return [
            'project' => 'APICS Phase I — OCBO, City of San Fernando, Pampanga',
            'overview' => $overview,
            'current_focus' => $currentFocus,
            'updated_at' => $updatedAt,
            'source' => self::PLAN_RELATIVE_PATH,
            'summary' => [
                'total' => $total,
                'completed' => $completed,
                'pending' => $pending,
                'in_progress' => $inProgress,
                'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
                'todos_total' => count($todos),
                'todos_completed' => $todosCompleted,
            ],
            'phases' => $phases,
            'roadmap' => [
                [
                    'id' => 'phase-ii',
                    'title' => 'Phase II (roadmap)',
                    'label' => 'Detailed CTO live payment integration',
                ],
                [
                    'id' => 'phase-iii',
                    'title' => 'Phase III (roadmap)',
                    'label' => 'As TOR clarifies — keep stubs and interface contracts ready',
                ],
            ],
            'todos' => $todos,
            'changelog' => $changelog,
            'next_steps' => $nextSteps,
        ];
    }

    /**
     * @return list<array{id: string, label: string, status: string}>
     */
    private function parseTodosFromMarkdown(string $contents): array
    {
        if ($contents === '' || ! preg_match('/^---\s*\n(.*?)\n---\s*/s', $contents, $matches)) {
            return $this->fallbackTodosFromConfig();
        }

        $frontmatter = $matches[1];
        $todos = [];

        if (! preg_match_all(
            '/-\s+id:\s*([^\n]+)\n\s+content:\s*(?:"([^"]*)"|([^\n]*))\n\s+status:\s*(\w+)/',
            $frontmatter,
            $blocks,
            PREG_SET_ORDER
        )) {
            return $this->fallbackTodosFromConfig();
        }

        foreach ($blocks as $block) {
            $todos[] = [
                'id' => trim($block[1]),
                'label' => trim($block[2] !== '' ? $block[2] : $block[3]),
                'status' => $this->normalizeStatus(trim($block[4])),
            ];
        }

        return $todos !== [] ? $todos : $this->fallbackTodosFromConfig();
    }

    private function parseOverviewFromMarkdown(string $contents): ?string
    {
        if ($contents === '' || ! preg_match('/^---\s*\n(.*?)\n---\s*/s', $contents, $matches)) {
            return null;
        }

        $frontmatter = $matches[1];
        if (preg_match('/^overview:\s*"([^"]*)"/m', $frontmatter, $overviewMatch)) {
            return trim($overviewMatch[1]);
        }
        if (preg_match('/^overview:\s*(.+)$/m', $frontmatter, $overviewMatch)) {
            return trim($overviewMatch[1], " \t\"'");
        }

        return null;
    }

    /**
     * @return list<array{date: string, completed: string, notes: string}>
     */
    private function parseChangelogFromMarkdown(string $contents): array
    {
        if ($contents === '') {
            return [];
        }

        if (! preg_match('/### Progress changelog\s*\n+(.*?)(?=\n### |\n## |\n---\s*\n|$)/s', $contents, $section)) {
            return [];
        }

        $rows = [];
        if (! preg_match_all(
            '/^\|\s*([^|]+?)\s*\|\s*([^|]+?)\s*\|\s*([^|]+?)\s*\|$/m',
            $section[1],
            $matches,
            PREG_SET_ORDER
        )) {
            return [];
        }

        foreach ($matches as $match) {
            $date = trim($match[1]);
            $completed = trim($match[2]);
            $notes = trim($match[3]);

            if ($date === '' || strcasecmp($date, 'Date') === 0 || preg_match('/^[-:]+$/', $date) === 1) {
                continue;
            }
            if ($date === '—' || strcasecmp($date, 'Next') === 0) {
                continue;
            }

            $rows[] = [
                'date' => $date,
                'completed' => $this->stripMarkdownInline($completed),
                'notes' => $this->stripMarkdownInline($notes),
            ];
        }

        // Newest first for the admin page.
        return array_reverse($rows);
    }

    /**
     * @return list<string>
     */
    private function parseNextStepsFromMarkdown(string $contents): array
    {
        if ($contents === '') {
            return [];
        }

        if (! preg_match('/## 12\.\s*Immediate next execution order\s*\n+(.*?)(?=\n## |\n---\s*\n|$)/s', $contents, $section)) {
            return [];
        }

        $steps = [];
        if (! preg_match_all('/^\d+\.\s+(.+)$/m', $section[1], $matches)) {
            return [];
        }

        foreach ($matches[1] as $line) {
            $cleaned = $this->stripMarkdownInline(trim($line));
            if ($cleaned !== '') {
                $steps[] = $cleaned;
            }
        }

        return $steps;
    }

    private function stripMarkdownInline(string $value): string
    {
        $value = preg_replace('/\*\*(.+?)\*\*/', '$1', $value) ?? $value;
        $value = preg_replace('/`([^`]+)`/', '$1', $value) ?? $value;
        $value = preg_replace('/~~(.+?)~~/', '$1', $value) ?? $value;

        return trim($value);
    }

    /**
     * Parse "### Phase N — Title (weeks…)" sections and their bullet items.
     *
     * @return array<int, array{number: int, title: string, weeks: string, bullets: list<string>}>
     */
    private function parseDeliveryPhasesFromMarkdown(string $contents): array
    {
        $phases = [];

        if ($contents === '') {
            return $phases;
        }

        if (! preg_match_all(
            '/### Phase (\d+)\s+[—\-]\s+([^\n]+)\n((?:(?!### Phase |\n---|\n## ).|\n)*?)(?=\n### Phase |\n---|\n## |$)/s',
            $contents,
            $matches,
            PREG_SET_ORDER
        )) {
            return $phases;
        }

        foreach ($matches as $match) {
            $number = (int) $match[1];
            $heading = trim($match[2]);
            $body = $match[3];

            $weeks = '';
            $title = $heading;
            if (preg_match('/^(.+?)\s*\(([^)]+)\)\s*$/', $heading, $parts)) {
                $title = trim($parts[1]);
                $weeks = trim($parts[2]);
            }

            $bullets = [];
            if (preg_match_all('/^- (.+)$/m', $body, $bulletMatches)) {
                foreach ($bulletMatches[1] as $bullet) {
                    $cleaned = trim(preg_replace('/\*\*(.+?)\*\*/', '$1', $bullet) ?? $bullet);
                    if ($cleaned !== '') {
                        $bullets[] = $cleaned;
                    }
                }
            }

            $phases[$number] = [
                'number' => $number,
                'title' => $title,
                'weeks' => $weeks,
                'bullets' => $bullets,
            ];
        }

        return $phases;
    }

    /**
     * @param  list<array{id: string, label: string, status: string}>  $todos
     * @param  array<int, array{number: int, title: string, weeks: string, bullets: list<string>}>  $parsed
     * @return list<array<string, mixed>>
     */
    private function buildDeliveryPhases(array $todos, array $parsed): array
    {
        $byId = [];
        foreach ($todos as $todo) {
            $byId[$todo['id']] = $todo;
        }

        $phases = [];

        for ($number = 0; $number <= 8; $number++) {
            $fallback = self::FALLBACK_PHASES[$number];
            $parsedPhase = $parsed[$number] ?? null;

            $title = $parsedPhase['title'] ?? $fallback['title'];
            $weeks = $parsedPhase['weeks'] ?? $fallback['weeks'];
            $bullets = ($parsedPhase['bullets'] ?? []) !== []
                ? $parsedPhase['bullets']
                : $fallback['items'];

            $linkedTodos = [];
            foreach (self::TODO_PHASE_MAP as $todoId => $phaseNumber) {
                if ($phaseNumber === $number && isset($byId[$todoId])) {
                    $linkedTodos[] = $byId[$todoId];
                }
            }

            $status = $this->resolvePhaseStatus($number, $linkedTodos);
            $items = $this->buildPhaseItems($bullets, $linkedTodos, $status);

            $completedItems = count(array_filter($items, fn (array $item): bool => $item['status'] === 'completed'));
            $totalItems = count($items);

            $phases[] = [
                'id' => 'phase-'.$number,
                'number' => $number,
                'title' => 'Phase '.$number.' — '.$title,
                'weeks' => $weeks,
                'status' => $status,
                'progress_percent' => $totalItems > 0
                    ? (int) round(($completedItems / $totalItems) * 100)
                    : ($status === 'completed' ? 100 : 0),
                'items' => $items,
                'todos' => $linkedTodos,
            ];
        }

        $markedNext = false;
        foreach ($phases as &$phase) {
            if (! $markedNext && $phase['status'] !== 'completed') {
                $phase['title'] .= ' (NEXT)';
                if ($phase['status'] === 'pending') {
                    $phase['status'] = 'in_progress';
                }
                $markedNext = true;
            }
        }
        unset($phase);

        return $phases;
    }

    /**
     * @param  list<array{id: string, label: string, status: string}>  $linkedTodos
     */
    private function resolvePhaseStatus(int $number, array $linkedTodos): string
    {
        if ($number === 8) {
            // Warranty starts after Phase 7 acceptance.
            return 'pending';
        }

        if ($linkedTodos === []) {
            // Phases 0–1 may have no dedicated todos after remapping; infer from later work.
            if ($number <= 1) {
                return 'completed';
            }

            return 'pending';
        }

        $completed = 0;
        $inProgress = 0;
        foreach ($linkedTodos as $todo) {
            if ($todo['status'] === 'completed') {
                $completed++;
            } elseif ($todo['status'] === 'in_progress') {
                $inProgress++;
            }
        }

        $total = count($linkedTodos);
        if ($completed === $total) {
            return 'completed';
        }
        if ($inProgress > 0 || $completed > 0) {
            return 'in_progress';
        }

        return 'pending';
    }

    /**
     * @param  list<string>  $bullets
     * @param  list<array{id: string, label: string, status: string}>  $linkedTodos
     * @return list<array{id: string, label: string, status: string}>
     */
    private function buildPhaseItems(array $bullets, array $linkedTodos, string $phaseStatus): array
    {
        $items = [];

        foreach ($bullets as $index => $bullet) {
            $items[] = [
                'id' => 'bullet-'.$index,
                'label' => $bullet,
                'status' => match ($phaseStatus) {
                    'completed' => 'completed',
                    'in_progress' => $index === 0 ? 'in_progress' : 'pending',
                    default => 'pending',
                },
            ];
        }

        foreach ($linkedTodos as $todo) {
            $items[] = [
                'id' => $todo['id'],
                'label' => '[Todo] '.$todo['label'],
                'status' => $todo['status'],
            ];
        }

        return $items;
    }

    /**
     * @param  list<array{id: string, label: string, status: string}>  $todos
     * @param  list<array<string, mixed>>  $phases
     */
    private function resolveCurrentFocus(array $todos, array $phases): string
    {
        foreach ($todos as $todo) {
            if ($todo['status'] === 'in_progress') {
                return $todo['label'];
            }
        }

        foreach ($phases as $phase) {
            if (($phase['status'] ?? '') === 'in_progress') {
                return (string) $phase['title'];
            }
        }

        foreach ($todos as $todo) {
            if ($todo['status'] === 'pending') {
                return $todo['label'];
            }
        }

        return 'Phase I delivery complete — monitor warranty / Phase II roadmap';
    }

    private function normalizeStatus(string $status): string
    {
        return match ($status) {
            'completed', 'complete', 'done' => 'completed',
            'in_progress', 'in-progress', 'active' => 'in_progress',
            'cancelled', 'canceled' => 'cancelled',
            default => 'pending',
        };
    }

    /**
     * @return list<array{id: string, label: string, status: string}>
     */
    private function fallbackTodosFromConfig(): array
    {
        /** @var array{phases?: list<array{items?: list<array{id: string, label: string, status: string}>}>} $plan */
        $plan = config('apics_project_plan', []);
        $todos = [];

        foreach ($plan['phases'] ?? [] as $phase) {
            foreach ($phase['items'] ?? [] as $item) {
                $todos[] = [
                    'id' => (string) $item['id'],
                    'label' => (string) $item['label'],
                    'status' => $this->normalizeStatus((string) $item['status']),
                ];
            }
        }

        return $todos;
    }
}
