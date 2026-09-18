<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\FormDefinition;
use App\Models\Inspection;
use App\Support\Inspection\InspectionFormCatalog;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class InspectionFormsTest extends TestCase
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

    private function submitAndReadyForInspection(string $title = 'Inspection Forms Project'): string
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

        $evaluatorToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'evaluator@csfp.local',
            'password' => 'Evaluate@123',
        ])->assertOk()->json('data.token');

        $evaluation = $this->actingAsApiToken($evaluatorToken)
            ->postJson("/api/v1/staff/applications/{$uuid}/evaluations", [
                'findings' => [['item' => 'Completeness', 'status' => 'ok']],
                'remarks' => 'Ready for inspection',
            ])
            ->assertCreated();

        $this->actingAsApiToken($evaluatorToken)
            ->postJson('/api/v1/staff/evaluations/'.$evaluation->json('data.uuid').'/decide', [
                'result' => 'compliant',
                'remarks' => 'Compliant',
            ])
            ->assertOk();

        return $uuid;
    }

    public function test_inspection_form_definitions_are_seeded(): void
    {
        foreach (['QMS-38', 'QMS-39', 'O-03', 'QMS-65', '77-006-E'] as $code) {
            $this->assertDatabaseHas('form_definitions', [
                'code' => $code,
                'is_active' => 1,
            ]);
        }

        $qms65 = FormDefinition::query()->where('code', 'QMS-65')->firstOrFail();
        $this->assertNotEmpty($qms65->schema['checklist'] ?? null);
    }

    public function test_staff_can_fetch_inspection_form_templates(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
        ])->assertOk()->json('data.token');

        $response = $this->actingAsApiToken($token)
            ->getJson('/api/v1/staff/inspection-form-templates')
            ->assertOk();

        $this->assertCount(5, $response->json('data.items'));
        $this->assertNotEmpty($response->json('data.qms65_default_items'));
        $this->assertSame('77-006-E', $response->json('data.electrical_blank.form_code'));
    }

    public function test_schedule_stores_qms38_team_and_complete_stores_structured_sheets(): void
    {
        $uuid = $this->submitAndReadyForInspection();

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
        ])->assertOk()->json('data.token');

        $inspection = $this->actingAsApiToken($token)
            ->postJson("/api/v1/staff/applications/{$uuid}/inspections", [
                'type' => 'joint_electrical',
                'location' => 'Project site',
                'schedule_sheet' => [
                    'purpose' => 'Joint electrical verification',
                    'meeting_point' => 'Main gate',
                    'disciplines' => ['electrical', 'structural'],
                    'coordination_notes' => 'Bring PPE',
                ],
                'team_inspectors' => [
                    ['name' => 'Ana Inspector', 'role' => 'Lead', 'discipline' => 'electrical'],
                    ['name' => 'Ben Partner', 'role' => 'Member', 'discipline' => 'structural'],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.schedule_sheet.purpose', 'Joint electrical verification')
            ->assertJsonPath('data.requires_electrical_form', true);

        $this->assertCount(2, $inspection->json('data.team_inspectors'));
        $inspectionUuid = $inspection->json('data.uuid');

        $complete = $this->actingAsApiToken($token)
            ->postJson("/api/v1/staff/inspections/{$inspectionUuid}/complete", [
                'result' => 'passed',
                'notes' => 'All clear after joint walkthrough',
                'inspector_notes' => [
                    'weather' => 'Sunny',
                    'site_conditions' => 'Clear access',
                    'findings' => 'Installations match approved plans',
                    'recommendations' => 'Proceed to payment',
                ],
                'compliance_sheet' => [
                    'form_code' => 'QMS-65',
                    'items' => array_map(
                        static fn (array $item): array => array_merge($item, ['status' => 'ok']),
                        InspectionFormCatalog::qms65DefaultItems(),
                    ),
                    'overall_remarks' => 'Compliant',
                ],
                'electrical_form' => [
                    'service_entrance' => 'ok',
                    'grounding' => 'ok',
                    'panel_boards' => 'ok',
                    'wiring_methods' => 'ok',
                    'fixtures_devices' => 'ok',
                    'load_schedule' => 'ok',
                    'result' => 'passed',
                    'remarks' => 'Final electrical OK',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.result', 'passed')
            ->assertJsonPath('data.inspector_notes.form_code', 'O-03')
            ->assertJsonPath('data.compliance_sheet.form_code', 'QMS-65')
            ->assertJsonPath('data.electrical_form.result', 'passed');

        $this->assertNotEmpty($complete->json('data.print_urls.qms-65'));
        $this->assertNotEmpty($complete->json('data.print_urls.o-03'));
        $this->assertNotEmpty($complete->json('data.print_urls.dpwh-77-006-e'));

        $model = Inspection::query()->where('uuid', $inspectionUuid)->firstOrFail();
        $this->assertSame('QMS-38', $model->schedule_sheet['form_code'] ?? null);
        $this->assertCount(2, $model->team_inspectors ?? []);
    }

    public function test_signed_inspection_print_is_allowed(): void
    {
        $uuid = $this->submitAndReadyForInspection('Print Inspection Project');

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
        ])->assertOk()->json('data.token');

        $inspectionUuid = $this->actingAsApiToken($token)
            ->postJson("/api/v1/staff/applications/{$uuid}/inspections", [
                'type' => 'joint_structural',
                'location' => 'Project site',
            ])
            ->assertCreated()
            ->json('data.uuid');

        $this->actingAsApiToken($token)
            ->postJson("/api/v1/staff/inspections/{$inspectionUuid}/complete", [
                'result' => 'passed',
                'notes' => 'Passed for print test',
                'compliance_sheet' => [
                    'items' => [['code' => 'SETBACK', 'label' => 'Setbacks', 'status' => 'ok']],
                ],
            ])
            ->assertOk();

        $this->get("/admin/inspections/{$inspectionUuid}/print?doc=qms-65")
            ->assertForbidden();

        $url = URL::temporarySignedRoute(
            'admin.inspections.print',
            now()->addMinutes(5),
            ['inspection' => $inspectionUuid, 'doc' => 'qms-65'],
        );

        $this->get($url)->assertOk()->assertSee('QMS-65', false);
    }

    public function test_staff_can_save_inspection_forms_without_completing(): void
    {
        $uuid = $this->submitAndReadyForInspection('Save Draft Inspection Project');

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
        ])->assertOk()->json('data.token');

        $inspectionUuid = $this->actingAsApiToken($token)
            ->postJson("/api/v1/staff/applications/{$uuid}/inspections", [
                'type' => 'joint_structural',
                'location' => 'Project site',
            ])
            ->assertCreated()
            ->json('data.uuid');

        $this->actingAsApiToken($token)
            ->postJson("/api/v1/staff/inspections/{$inspectionUuid}/forms", [
                'notes' => 'Partial findings for later',
                'inspector_notes' => [
                    'weather' => 'Cloudy',
                    'findings' => 'Rough-in checked; finish pending',
                ],
                'compliance_sheet' => [
                    'items' => [
                        ['code' => 'SETBACK', 'label' => 'Setbacks', 'status' => 'ok'],
                        ['code' => 'STRUCTURAL', 'label' => 'Structural', 'status' => 'na'],
                    ],
                    'overall_remarks' => 'Draft save',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.result', null)
            ->assertJsonPath('data.inspector_notes.findings', 'Rough-in checked; finish pending')
            ->assertJsonPath('data.compliance_sheet.overall_remarks', 'Draft save');

        $this->assertDatabaseHas('permit_applications', [
            'uuid' => $uuid,
            'status' => 'for_inspection',
        ]);

        $this->assertDatabaseHas('audit_logs', ['event' => 'inspection.forms_saved']);
    }
}
