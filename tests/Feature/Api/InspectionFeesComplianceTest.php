<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InspectionFeesComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Sanctum RequestGuard caches the user across in-process HTTP test requests.
     */
    private function actingAsApiToken(string $token): static
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        return $this->withToken($token);
    }

    private function submitApplication(string $title = 'Inspection Fee Project'): string
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

    private function decideCompliant(string $applicationUuid): void
    {
        $evaluatorToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'evaluator@csfp.local',
            'password' => 'Evaluate@123',
        ])->assertOk()->json('data.token');

        $evaluation = $this->actingAsApiToken($evaluatorToken)
            ->postJson("/api/v1/staff/applications/{$applicationUuid}/evaluations", [
                'findings' => [['item' => 'Completeness', 'status' => 'ok']],
                'remarks' => 'Ready for inspection',
            ])
            ->assertCreated();

        $this->actingAsApiToken($evaluatorToken)
            ->postJson('/api/v1/staff/evaluations/'.$evaluation->json('data.uuid').'/decide', [
                'result' => 'compliant',
                'remarks' => 'Compliant',
            ])
            ->assertOk()
            ->assertJsonPath('data.next_step.step', 'inspection')
            ->assertJsonPath('data.next_step.path', '/admin/inspections');

        $this->assertDatabaseHas('permit_applications', [
            'uuid' => $applicationUuid,
            'status' => 'for_inspection',
        ]);
    }

    public function test_staff_can_schedule_inspection_generate_oop_and_issue_notice(): void
    {
        $uuid = $this->submitApplication();
        $this->decideCompliant($uuid);

        $inspectorToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
        ])->assertOk()->json('data.token');

        $inspection = $this->actingAsApiToken($inspectorToken)
            ->postJson("/api/v1/staff/applications/{$uuid}/inspections", [
                'type' => 'joint',
                'location' => 'Project site',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'scheduled');

        $inspectionUuid = $inspection->json('data.uuid');

        $this->actingAsApiToken($inspectorToken)
            ->postJson("/api/v1/staff/inspections/{$inspectionUuid}/complete", [
                'result' => 'passed',
                'notes' => 'All clear',
                'compliance_sheet' => [['item' => 'Structure', 'status' => 'ok']],
            ])
            ->assertOk()
            ->assertJsonPath('data.result', 'passed')
            ->assertJsonPath('data.next_step.step', 'payment')
            ->assertJsonPath('data.next_step.path', '/admin/orders-of-payment');

        $assessorToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'assessor@csfp.local',
            'password' => 'Assess@1234',
        ])->assertOk()->json('data.token');

        $preview = $this->actingAsApiToken($assessorToken)
            ->getJson("/api/v1/staff/applications/{$uuid}/orders-of-payment/preview")
            ->assertOk();
        $this->assertNotEmpty($preview->json('data.lines'));
        $this->assertGreaterThan(0, (float) $preview->json('data.total'));

        $oop = $this->actingAsApiToken($assessorToken)
            ->postJson("/api/v1/staff/applications/{$uuid}/orders-of-payment")
            ->assertCreated();

        $this->assertNotEmpty($oop->json('data.oop_no'));
        $this->assertNotEmpty($oop->json('data.cto_stub_reference'));
        $this->assertGreaterThan(0, (float) $oop->json('data.total_amount'));
        $this->assertNotEmpty($oop->json('data.lines'));

        $list = $this->actingAsApiToken($assessorToken)
            ->getJson('/api/v1/staff/orders-of-payment')
            ->assertOk();
        $this->assertArrayHasKey('issued', $list->json('data.summary') ?? []);
        $this->assertGreaterThanOrEqual(1, (int) $list->json('data.summary.issued'));
        $this->assertNotEmpty($list->json('data.items.0.application.application_no'));
        $this->assertNotEmpty($list->json('data.items.0.lines'));

        $oopUuid = $oop->json('data.uuid');

        $this->actingAsApiToken($assessorToken)
            ->postJson("/api/v1/staff/orders-of-payment/{$oopUuid}/mark-paid")
            ->assertOk()
            ->assertJsonPath('data.status', 'paid_stub')
            ->assertJsonPath('data.next_step.step', 'releasing')
            ->assertJsonPath('data.next_step.path', '/admin/logbooks')
            ->assertJsonPath(
                'data.next_step.message',
                'Payment cleared — proceed to the Releasing area. Status becomes Released only after G-01 logbook release.',
            );

        $this->assertDatabaseHas('permit_applications', [
            'uuid' => $uuid,
            'status' => 'for_releasing',
        ]);

        $this->assertDatabaseHas('audit_logs', ['event' => 'inspection.scheduled']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'order_of_payment.issued']);
    }

    public function test_cannot_schedule_inspection_before_evaluation(): void
    {
        $uuid = $this->submitApplication('Skip Eval Project');

        $inspectorToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
        ])->assertOk()->json('data.token');

        $this->actingAsApiToken($inspectorToken)
            ->postJson("/api/v1/staff/applications/{$uuid}/inspections", [
                'type' => 'joint',
                'location' => 'Project site',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['application']);
    }

    public function test_cannot_schedule_second_open_inspection_for_same_application(): void
    {
        $uuid = $this->submitApplication('Duplicate Inspection Guard');
        $this->decideCompliant($uuid);

        $inspectorToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
        ])->assertOk()->json('data.token');

        $this->actingAsApiToken($inspectorToken)
            ->postJson("/api/v1/staff/applications/{$uuid}/inspections", [
                'type' => 'joint_structural',
                'location' => 'Project site',
            ])
            ->assertCreated();

        $this->actingAsApiToken($inspectorToken)
            ->postJson("/api/v1/staff/applications/{$uuid}/inspections", [
                'type' => 'joint_architectural',
                'location' => 'Project site',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['application']);

        $openForApp = \App\Models\Inspection::query()
            ->whereHas('application', fn ($q) => $q->where('uuid', $uuid))
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->count();

        $this->assertSame(1, $openForApp);
    }

    public function test_failed_inspection_opens_compliance_and_blocks_oop(): void
    {
        $uuid = $this->submitApplication('Compliance Branch Project');
        $this->decideCompliant($uuid);

        $inspectorToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
        ])->assertOk()->json('data.token');

        $inspectionUuid = $this->actingAsApiToken($inspectorToken)
            ->postJson("/api/v1/staff/applications/{$uuid}/inspections", [
                'type' => 'joint',
                'location' => 'Project site',
            ])
            ->assertCreated()
            ->json('data.uuid');

        $this->actingAsApiToken($inspectorToken)
            ->postJson("/api/v1/staff/inspections/{$inspectionUuid}/complete", [
                'result' => 'failed',
                'notes' => 'Deficiencies found',
                'compliance_sheet' => [['item' => 'Setbacks', 'status' => 'fail']],
            ])
            ->assertOk()
            ->assertJsonPath('data.next_step.step', 'compliance');

        $assessorToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'assessor@csfp.local',
            'password' => 'Assess@1234',
        ])->assertOk()->json('data.token');

        $this->actingAsApiToken($assessorToken)
            ->postJson("/api/v1/staff/applications/{$uuid}/orders-of-payment")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['application']);

        $complianceToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'compliance@csfp.local',
            'password' => 'Comply@1234',
        ])->assertOk()->json('data.token');

        $notice = $this->actingAsApiToken($complianceToken)
            ->postJson("/api/v1/staff/applications/{$uuid}/compliance-notices", [
                'type' => 'g03_compliance',
                'title' => 'Notice of Compliance',
                'body' => 'Please address the remaining documentary requirements within fifteen days.',
                'inspection_uuid' => $inspectionUuid,
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'g03_compliance');

        $noticeUuid = $notice->json('data.uuid');

        $list = $this->actingAsApiToken($complianceToken)
            ->getJson('/api/v1/staff/compliance-notices')
            ->assertOk();
        $this->assertArrayHasKey('issued', $list->json('data.summary') ?? []);
        $this->assertGreaterThanOrEqual(1, (int) $list->json('data.summary.total'));

        $appeal = $this->actingAsApiToken($complianceToken)
            ->postJson("/api/v1/staff/compliance-notices/{$noticeUuid}/appeals", [
                'grounds' => 'Requesting re-evaluation of cited documentary deficiencies.',
            ])
            ->assertCreated();

        $listAppealed = $this->actingAsApiToken($complianceToken)
            ->getJson('/api/v1/staff/compliance-notices?status=appealed')
            ->assertOk();
        $this->assertTrue(collect($listAppealed->json('data.items'))->contains(
            fn ($row) => ($row['uuid'] ?? null) === $noticeUuid
        ));
        $this->assertNotEmpty($listAppealed->json('data.items.0.pending_appeal_uuid'));

        $appealUuid = $appeal->json('data.uuid');

        $this->actingAsApiToken($complianceToken)
            ->postJson("/api/v1/staff/compliance-appeals/{$appealUuid}/resolve", [
                'status' => 'upheld',
                'resolution_notes' => 'Appeal granted; reopen evaluation.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'upheld');

        $this->assertDatabaseHas('audit_logs', ['event' => 'compliance_notice.issued']);
    }

    public function test_admin_can_manage_fee_rules(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
        ])->json('data.token');

        $created = $this->actingAsApiToken($token)
            ->postJson('/api/v1/admin/fee-rules', [
                'code' => 'TEST-FEE',
                'name' => 'Test fee',
                'agency' => 'lgu',
                'basis' => 'fixed',
                'amount' => 100,
                'priority' => 50,
                'is_active' => true,
                'conditions' => [
                    ['field' => 'lot_area', 'operator' => '>=', 'value' => '50'],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'TEST-FEE')
            ->assertJsonPath('data.conditions.0.field', 'lot_area')
            ->assertJsonPath('data.conditions.0.operator', '>=')
            ->assertJsonPath('data.condition_count', 1);

        $uuid = $created->json('data.uuid');
        $this->actingAsApiToken($token)
            ->putJson("/api/v1/admin/fee-rules/{$uuid}", [
                'name' => 'Test fee updated',
                'amount' => 250.5,
                'priority' => 40,
                'is_active' => false,
                'conditions' => [
                    ['field' => 'lot_area', 'operator' => '>=', 'value' => 100],
                    ['field' => 'occupancy', 'operator' => 'contains', 'value' => 'Commercial'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Test fee updated')
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.condition_count', 2)
            ->assertJsonPath('data.conditions.0.value', 100);

        $fees = $this->actingAsApiToken($token)
            ->getJson('/api/v1/admin/fee-rules')
            ->assertOk();
        $this->assertGreaterThanOrEqual(1, (int) $fees->json('data.summary.lgu'));
        $this->assertArrayHasKey('total', $fees->json('data.summary') ?? []);
        $this->assertDatabaseHas('audit_logs', ['event' => 'fee_rule.updated']);
    }

    public function test_applicant_cannot_list_inspections(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->json('data.token');

        $this->actingAsApiToken($token)
            ->getJson('/api/v1/staff/inspections')
            ->assertForbidden();
    }

    public function test_staff_can_lookup_applications_for_pickers(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
        ])->assertOk()->json('data.token');

        $response = $this->actingAsApiToken($token)
            ->getJson('/api/v1/staff/applications/lookup?search=&per_page=10')
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertNotEmpty($response->json('data.items'));
        $this->assertArrayHasKey('uuid', $response->json('data.items.0'));
        $this->assertArrayHasKey('application_no', $response->json('data.items.0'));
        $this->assertArrayNotHasKey('payload', $response->json('data.items.0'));
    }

    public function test_lookup_for_step_filters_eligible_statuses(): void
    {
        $uuid = $this->submitApplication('Lookup Gate Project');
        $this->decideCompliant($uuid);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
        ])->assertOk()->json('data.token');

        $inspectionLookup = $this->actingAsApiToken($token)
            ->getJson('/api/v1/staff/applications/lookup?for_step=inspection&per_page=50')
            ->assertOk()
            ->json('data.items');

        $this->assertTrue(collect($inspectionLookup)->contains(fn ($row) => ($row['uuid'] ?? null) === $uuid));

        $paymentLookup = $this->actingAsApiToken($token)
            ->getJson('/api/v1/staff/applications/lookup?for_step=payment&per_page=50')
            ->assertOk()
            ->json('data.items');

        $this->assertFalse(collect($paymentLookup)->contains(fn ($row) => ($row['uuid'] ?? null) === $uuid));
    }

    public function test_applicant_cannot_lookup_staff_applications(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->json('data.token');

        $this->actingAsApiToken($token)
            ->getJson('/api/v1/staff/applications/lookup')
            ->assertForbidden();
    }

    public function test_failed_inspection_requires_reason_and_applicant_can_see_notice_and_appeal(): void
    {
        $uuid = $this->submitApplication('Failure Reason Visibility Project');
        $this->decideCompliant($uuid);

        $inspectorToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
        ])->assertOk()->json('data.token');

        $inspectionUuid = $this->actingAsApiToken($inspectorToken)
            ->postJson("/api/v1/staff/applications/{$uuid}/inspections", [
                'type' => 'joint',
                'location' => 'Project site',
            ])
            ->assertCreated()
            ->json('data.uuid');

        $this->actingAsApiToken($inspectorToken)
            ->postJson("/api/v1/staff/inspections/{$inspectionUuid}/complete", [
                'result' => 'failed',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['notes']);

        $failureReason = 'Missing firewall separation between units and incomplete electrical grounding at main panel.';

        $this->actingAsApiToken($inspectorToken)
            ->postJson("/api/v1/staff/inspections/{$inspectionUuid}/complete", [
                'result' => 'failed',
                'notes' => $failureReason,
                'compliance_sheet' => [['item' => 'Fire and electrical', 'status' => 'fail']],
            ])
            ->assertOk()
            ->assertJsonPath('data.notes', $failureReason);

        $complianceToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'compliance@csfp.local',
            'password' => 'Comply@1234',
        ])->assertOk()->json('data.token');

        $noticeUuid = $this->actingAsApiToken($complianceToken)
            ->postJson("/api/v1/staff/applications/{$uuid}/compliance-notices", [
                'type' => 'g03_compliance',
                'title' => 'Correct inspection deficiencies',
                'body' => "Inspection findings:\n\n{$failureReason}\n\nPlease correct within fifteen days.",
            ])
            ->assertCreated()
            ->assertJsonPath('data.inspection.notes', $failureReason)
            ->json('data.uuid');

        $applicantToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $detail = $this->actingAsApiToken($applicantToken)
            ->getJson("/api/v1/applications/{$uuid}")
            ->assertOk();

        $notices = collect($detail->json('data.compliance_notices') ?? []);
        $this->assertTrue($notices->contains(fn ($row) => ($row['uuid'] ?? null) === $noticeUuid));
        $matched = $notices->firstWhere('uuid', $noticeUuid);
        $this->assertStringContainsString('Missing firewall', (string) ($matched['body'] ?? ''));
        $this->assertSame($failureReason, $matched['inspection']['notes'] ?? null);

        $this->actingAsApiToken($applicantToken)
            ->postJson("/api/v1/compliance-notices/{$noticeUuid}/appeals", [
                'grounds' => 'Firewall work was completed before the inspection date; requesting re-inspection.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('compliance_notices', [
            'uuid' => $noticeUuid,
            'status' => 'appealed',
        ]);
    }
}
