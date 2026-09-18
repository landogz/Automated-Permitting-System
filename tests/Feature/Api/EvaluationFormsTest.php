<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Department;
use App\Models\Evaluation;
use App\Models\FormDefinition;
use App\Support\Evaluation\EvaluationFormCatalog;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EvaluationFormsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function actingAsApiToken(string $token): static
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        return $this->withToken($token);
    }

    private function evaluatorToken(): string
    {
        return $this->postJson('/api/v1/auth/login', [
            'email' => 'evaluator@csfp.local',
            'password' => 'Evaluate@123',
        ])->assertOk()->json('data.token');
    }

    private function submitApplication(string $title = 'Evaluation Forms Project'): string
    {
        $applicantToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $draft = $this->actingAsApiToken($applicantToken)->postJson('/api/v1/applications', [
            'project_title' => $title,
            'project_location' => 'San Fernando, Pampanga',
            'payload' => ['owner_name' => 'Demo', 'lot_area' => 250, 'occupancy' => 'Commercial'],
        ])->assertCreated();

        $uuid = $draft->json('data.uuid');

        $this->actingAsApiToken($applicantToken)
            ->postJson("/api/v1/applications/{$uuid}/submit")
            ->assertOk();

        return $uuid;
    }

    public function test_repeated_create_reuses_single_draft(): void
    {
        $uuid = $this->submitApplication('Single Draft Project');
        $token = $this->evaluatorToken();

        $first = $this->actingAsApiToken($token)
            ->postJson("/api/v1/staff/applications/{$uuid}/evaluations", [
                'findings' => [
                    'completeness' => EvaluationFormCatalog::qms63DefaultItems(),
                    'technical' => EvaluationFormCatalog::qms64DefaultItems(),
                ],
                'remarks' => 'First save',
            ])
            ->assertCreated();

        $second = $this->actingAsApiToken($token)
            ->postJson("/api/v1/staff/applications/{$uuid}/evaluations", [
                'findings' => [
                    'completeness' => array_map(
                        static fn (array $item): array => [...$item, 'status' => 'ok'],
                        EvaluationFormCatalog::qms63DefaultItems(),
                    ),
                    'technical' => EvaluationFormCatalog::qms64DefaultItems(),
                    'overall_remarks' => 'Continued draft',
                ],
                'remarks' => 'Second save',
            ])
            ->assertCreated();

        $this->assertSame($first->json('data.uuid'), $second->json('data.uuid'));
        $this->assertSame('ok', $second->json('data.findings.completeness.0.status'));
        $this->assertSame(1, Evaluation::query()->where('status', 'draft')->count());
    }

    public function test_evaluation_form_definitions_are_seeded(): void
    {
        foreach (['QMS-61', 'QMS-62', 'QMS-63', 'QMS-64'] as $code) {
            $this->assertDatabaseHas('form_definitions', [
                'code' => $code,
                'is_active' => 1,
            ]);
        }

        $qms63 = FormDefinition::query()->where('code', 'QMS-63')->firstOrFail();
        $this->assertNotEmpty($qms63->schema['checklist'] ?? null);
    }

    public function test_staff_can_fetch_evaluation_form_templates(): void
    {
        $token = $this->evaluatorToken();

        $response = $this->actingAsApiToken($token)
            ->getJson('/api/v1/staff/evaluation-form-templates')
            ->assertOk();

        $this->assertCount(4, $response->json('data.items'));
        $this->assertNotEmpty($response->json('data.qms63_default_items'));
        $this->assertNotEmpty($response->json('data.qms64_default_items'));
        $this->assertSame('QMS-63', $response->json('data.items.2.form_code'));
    }

    public function test_structured_findings_save_decide_and_print_urls(): void
    {
        $uuid = $this->submitApplication();
        $token = $this->evaluatorToken();

        $completeness = EvaluationFormCatalog::qms63DefaultItems();
        $completeness[0]['status'] = 'ok';
        $technical = EvaluationFormCatalog::qms64DefaultItems();
        $technical[0]['status'] = 'ok';

        $created = $this->actingAsApiToken($token)
            ->postJson("/api/v1/staff/applications/{$uuid}/evaluations", [
                'findings' => [
                    'completeness' => $completeness,
                    'technical' => $technical,
                    'overall_remarks' => 'Docs look complete',
                    'discipline_remarks' => 'Structural OK',
                ],
                'remarks' => 'Draft sheet',
            ])
            ->assertCreated();

        $evaluationUuid = $created->json('data.uuid');
        $this->assertSame('ok', $created->json('data.findings.completeness.0.status'));
        $this->assertNotEmpty($created->json('data.print_urls.qms-63'));
        $this->assertNotEmpty($created->json('data.print_urls.qms-64'));

        $this->actingAsApiToken($token)
            ->postJson("/api/v1/staff/evaluations/{$evaluationUuid}/forms", [
                'findings' => [
                    'completeness' => $completeness,
                    'technical' => $technical,
                    'overall_remarks' => 'Updated overall',
                    'discipline_remarks' => 'Updated discipline',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.findings.overall_remarks', 'Updated overall');

        $this->actingAsApiToken($token)
            ->postJson("/api/v1/staff/evaluations/{$evaluationUuid}/decide", [
                'result' => 'compliant',
                'findings' => [
                    'completeness' => $completeness,
                    'technical' => $technical,
                    'overall_remarks' => 'Final overall',
                    'discipline_remarks' => 'Final discipline',
                ],
                'remarks' => 'Compliant',
            ])
            ->assertOk()
            ->assertJsonPath('data.result', 'compliant');

        $evaluation = Evaluation::query()->where('uuid', $evaluationUuid)->firstOrFail();
        $printUrl = URL::temporarySignedRoute(
            'admin.evaluations.print',
            now()->addMinutes(15),
            ['evaluation' => $evaluation->uuid, 'doc' => 'qms-63'],
        );

        $this->get($printUrl)->assertOk()->assertSee('QMS-63', false);
    }

    public function test_timer_attaches_department_and_time_summary_rolls_up(): void
    {
        $uuid = $this->submitApplication('Timer Department Project');
        $token = $this->evaluatorToken();
        $department = Department::query()->firstOrFail();

        $this->actingAsApiToken($token)
            ->postJson("/api/v1/staff/applications/{$uuid}/timer/start", [
                'department_uuid' => $department->uuid,
                'notes' => 'Architectural review',
            ])
            ->assertCreated()
            ->assertJsonPath('data.department.uuid', $department->uuid);

        $this->actingAsApiToken($token)
            ->postJson('/api/v1/staff/timer/stop')
            ->assertOk();

        $summary = $this->actingAsApiToken($token)
            ->getJson('/api/v1/staff/evaluation-time-summary')
            ->assertOk();

        $this->assertIsArray($summary->json('data.by_department'));
        $this->assertIsArray($summary->json('data.by_staff'));
        $this->assertGreaterThanOrEqual(0, (int) $summary->json('data.total_seconds'));
        $deptUuids = collect($summary->json('data.by_department'))->pluck('department.uuid')->filter()->all();
        $this->assertContains($department->uuid, $deptUuids);
    }

    public function test_routing_slip_print_urls_are_signed(): void
    {
        $uuid = $this->submitApplication('Routing Print Project');
        $token = $this->evaluatorToken();

        $this->actingAsApiToken($token)
            ->postJson("/api/v1/staff/applications/{$uuid}/classify", ['auto' => true])
            ->assertOk();

        $slip = $this->actingAsApiToken($token)
            ->postJson("/api/v1/staff/applications/{$uuid}/routing-slip")
            ->assertCreated();

        $this->assertNotEmpty($slip->json('data.print_urls.qms-61'));
        $this->assertNotEmpty($slip->json('data.print_urls.qms-62'));

        $printUrl = $slip->json('data.print_urls.qms-61');
        $this->get($printUrl)->assertOk()->assertSee('QMS-61', false);
    }
}
