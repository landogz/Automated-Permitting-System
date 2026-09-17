<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassifierRoutingEvaluationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Sanctum RequestGuard caches the user across in-process HTTP test requests.
     * Clear it when switching bearer tokens between personas.
     */
    private function actingAsApiToken(string $token): static
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        return $this->withToken($token);
    }

    public function test_staff_can_classify_generate_routing_slip_and_evaluate(): void
    {
        $applicantToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $draft = $this->actingAsApiToken($applicantToken)->postJson('/api/v1/applications', [
            'project_title' => 'Large Warehouse',
            'project_location' => 'Sindalan, CSFP',
            'payload' => ['owner_name' => 'Demo', 'lot_area' => 1200, 'occupancy' => 'Storage'],
        ])->assertCreated();

        $uuid = $draft->json('data.uuid');

        $this->actingAsApiToken($applicantToken)
            ->postJson("/api/v1/applications/{$uuid}/submit")
            ->assertOk();

        $staffToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'evaluator@csfp.local',
            'password' => 'Evaluate@123',
        ])->assertOk()->json('data.token');

        $this->actingAsApiToken($staffToken)
            ->getJson("/api/v1/staff/applications/{$uuid}/suggest-classification")
            ->assertOk()
            ->assertJsonPath('data.classification', 'highly_technical');

        $this->actingAsApiToken($staffToken)
            ->postJson("/api/v1/staff/applications/{$uuid}/classify", ['auto' => true])
            ->assertOk()
            ->assertJsonPath('data.classification', 'highly_technical')
            ->assertJsonPath('data.status', 'under_evaluation');

        $slip = $this->actingAsApiToken($staffToken)
            ->postJson("/api/v1/staff/applications/{$uuid}/routing-slip")
            ->assertCreated();

        $this->assertNotEmpty($slip->json('data.slip_no'));
        $this->assertGreaterThanOrEqual(1, count($slip->json('data.steps') ?? []));

        $evaluation = $this->actingAsApiToken($staffToken)
            ->postJson("/api/v1/staff/applications/{$uuid}/evaluations", [
                'findings' => [['item' => 'Plans', 'status' => 'ok']],
                'remarks' => 'Looks good',
            ])
            ->assertCreated();

        $evaluationUuid = $evaluation->json('data.uuid');

        $this->actingAsApiToken($staffToken)
            ->postJson("/api/v1/staff/evaluations/{$evaluationUuid}/decide", [
                'result' => 'compliant',
                'remarks' => 'Approved for next stage',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'decided')
            ->assertJsonPath('data.result', 'compliant')
            ->assertJsonPath('data.next_step.step', 'inspection');

        $this->assertDatabaseHas('permit_applications', [
            'uuid' => $uuid,
            'classification' => 'highly_technical',
            'status' => 'for_inspection',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'permit_application.classified',
        ]);
    }

    public function test_admin_can_manage_classification_rules(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
        ])->json('data.token');

        $created = $this->actingAsApiToken($token)
            ->postJson('/api/v1/admin/classification-rules', [
                'code' => 'TEST-RULE',
                'name' => 'Test rule',
                'classification' => 'simple',
                'priority' => 5,
                'sla_hours' => 24,
                'conditions' => [
                    ['field' => 'lot_area', 'operator' => '<', 'value' => 50],
                ],
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'TEST-RULE');

        $list = $this->actingAsApiToken($token)
            ->getJson('/api/v1/admin/classification-rules')
            ->assertOk();
        $this->assertArrayHasKey('active', $list->json('data.summary') ?? []);
        $this->assertGreaterThanOrEqual(1, (int) $list->json('data.summary.total'));

        $uuid = $created->json('data.uuid');
        $this->actingAsApiToken($token)
            ->putJson("/api/v1/admin/classification-rules/{$uuid}", [
                'name' => 'Test rule updated',
                'priority' => 8,
                'sla_hours' => 48,
                'is_active' => false,
                'conditions' => [
                    ['field' => 'lot_area', 'operator' => '<', 'value' => 40],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Test rule updated')
            ->assertJsonPath('data.priority', 8)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('audit_logs', ['event' => 'classification_rule.updated']);
    }

    public function test_admin_can_update_routing_template(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
        ])->json('data.token');

        $deptUuid = $this->actingAsApiToken($token)
            ->getJson('/api/v1/admin/departments?per_page=1')
            ->assertOk()
            ->json('data.items.0.uuid');

        $this->assertNotEmpty($deptUuid);

        $created = $this->actingAsApiToken($token)
            ->postJson('/api/v1/admin/routing-templates', [
                'code' => 'TEST-RT',
                'name' => 'Test routing',
                'classification' => 'simple',
                'is_active' => true,
                'steps' => [
                    [
                        'department_uuid' => $deptUuid,
                        'label' => 'Initial review',
                        'step_order' => 1,
                        'sla_hours' => 24,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'TEST-RT');

        $uuid = $created->json('data.uuid');
        $this->actingAsApiToken($token)
            ->putJson("/api/v1/admin/routing-templates/{$uuid}", [
                'name' => 'Test routing updated',
                'is_active' => false,
                'steps' => [
                    [
                        'department_uuid' => $deptUuid,
                        'label' => 'Revised review',
                        'step_order' => 1,
                        'sla_hours' => 36,
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Test routing updated')
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.steps.0.label', 'Revised review');

        $this->assertDatabaseHas('audit_logs', ['event' => 'routing_template.updated']);
    }

    public function test_evaluator_can_browse_routing_templates_readonly(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'evaluator@csfp.local',
            'password' => 'Evaluate@123',
        ])->assertOk()->json('data.token');

        $response = $this->actingAsApiToken($token)
            ->getJson('/api/v1/staff/routing-templates')
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertFalse((bool) $response->json('data.can_manage'));
        $this->assertIsArray($response->json('data.items'));
    }

    public function test_applicant_cannot_access_staff_queue(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->json('data.token');

        $this->actingAsApiToken($token)
            ->getJson('/api/v1/staff/queue')
            ->assertForbidden();
    }

    public function test_inspector_cannot_access_evaluation_queue(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
        ])->assertOk()->json('data.token');

        $this->actingAsApiToken($token)
            ->getJson('/api/v1/staff/queue')
            ->assertForbidden();
    }

    public function test_login_redirects_inspector_to_inspections(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
        ])
            ->assertOk()
            ->assertJsonPath('data.redirect', '/admin/inspections');
    }
}
