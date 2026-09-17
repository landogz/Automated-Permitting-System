<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\FormDefinition;
use App\Models\PermitApplication;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationDocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('local');
    }

    public function test_applicant_can_upload_and_replace_required_document(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $form = FormDefinition::query()->where('code', 'QMS-36')->firstOrFail();

        $draft = $this->withToken($token)->postJson('/api/v1/applications', [
            'form_definition_uuid' => $form->uuid,
            'project_title' => 'Upload test building',
            'project_location' => 'San Fernando, Pampanga',
            'payload' => ['owner_name' => 'Juan Dela Cruz'],
        ])->assertCreated();

        $uuid = $draft->json('data.uuid');

        $file = UploadedFile::fake()->create('lot-plan.pdf', 200, 'application/pdf');

        $upload = $this->withToken($token)->post(
            "/api/v1/applications/{$uuid}/documents",
            [
                'label' => 'lot_plan',
                'file' => $file,
            ],
            ['Accept' => 'application/json'],
        );

        $upload->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.label', 'lot_plan');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'permit_application.document_uploaded',
        ]);

        $docUuid = $upload->json('data.uuid');

        $this->withToken($token)
            ->get("/api/v1/applications/{$uuid}/documents/{$docUuid}/file")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $replacement = UploadedFile::fake()->image('lot-plan.jpg');
        $this->withToken($token)->post(
            "/api/v1/applications/{$uuid}/documents",
            [
                'label' => 'lot_plan',
                'file' => $replacement,
            ],
            ['Accept' => 'application/json'],
        )->assertCreated();

        $this->assertSame(
            1,
            PermitApplication::query()->where('uuid', $uuid)->firstOrFail()->documents()->count()
        );

        $this->withToken($token)
            ->deleteJson("/api/v1/applications/{$uuid}/documents/{$docUuid}")
            ->assertOk();
    }

    public function test_applicant_can_update_and_upload_when_submitted(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $form = FormDefinition::query()->where('code', 'QMS-36')->firstOrFail();
        $required = is_array($form->required_attachments) ? $form->required_attachments : [];

        $uuid = $this->withToken($token)->postJson('/api/v1/applications', [
            'form_definition_uuid' => $form->uuid,
            'project_title' => 'Submitted editable building',
            'project_location' => 'San Fernando, Pampanga',
            'payload' => ['owner_name' => 'Juan Dela Cruz'],
        ])->assertCreated()->json('data.uuid');

        foreach ($required as $label) {
            $this->withToken($token)->post(
                "/api/v1/applications/{$uuid}/documents",
                [
                    'label' => $label,
                    'file' => UploadedFile::fake()->create($label.'.pdf', 100, 'application/pdf'),
                ],
                ['Accept' => 'application/json'],
            )->assertCreated();
        }

        $this->withToken($token)
            ->postJson("/api/v1/applications/{$uuid}/submit")
            ->assertOk()
            ->assertJsonPath('data.status', 'submitted');

        $this->withToken($token)
            ->putJson("/api/v1/applications/{$uuid}", [
                'project_title' => 'Submitted editable building (revised)',
                'project_location' => 'San Fernando, Pampanga',
                'payload' => ['owner_name' => 'Juan Dela Cruz', 'notes' => 'Updated after submit'],
            ])
            ->assertOk()
            ->assertJsonPath('data.project_title', 'Submitted editable building (revised)')
            ->assertJsonPath('data.status', 'submitted');

        $this->withToken($token)->post(
            "/api/v1/applications/{$uuid}/documents",
            [
                'label' => $required[0] ?? 'lot_plan',
                'file' => UploadedFile::fake()->create('corrected.pdf', 120, 'application/pdf'),
            ],
            ['Accept' => 'application/json'],
        )->assertCreated();

        $this->assertDatabaseHas('permit_applications', [
            'uuid' => $uuid,
            'status' => 'submitted',
            'project_title' => 'Submitted editable building (revised)',
        ]);
    }

    public function test_applicant_cannot_edit_released_application(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $form = FormDefinition::query()->where('code', 'QMS-36')->firstOrFail();

        $uuid = $this->withToken($token)->postJson('/api/v1/applications', [
            'form_definition_uuid' => $form->uuid,
            'project_title' => 'Released lock test',
            'project_location' => 'San Fernando, Pampanga',
            'payload' => ['owner_name' => 'Lock'],
        ])->assertCreated()->json('data.uuid');

        PermitApplication::query()->where('uuid', $uuid)->update(['status' => 'released']);

        $this->withToken($token)
            ->putJson("/api/v1/applications/{$uuid}", [
                'project_title' => 'Should fail',
                'project_location' => 'San Fernando, Pampanga',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_submit_requires_form_attachments(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $form = FormDefinition::query()->where('code', 'QMS-36')->firstOrFail();

        $uuid = $this->withToken($token)->postJson('/api/v1/applications', [
            'form_definition_uuid' => $form->uuid,
            'project_title' => 'Needs docs',
            'project_location' => 'San Fernando, Pampanga',
            'payload' => ['owner_name' => 'Maria'],
        ])->assertCreated()->json('data.uuid');

        $this->withToken($token)
            ->postJson("/api/v1/applications/{$uuid}/submit")
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_rejects_disallowed_mime_type(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $form = FormDefinition::query()->where('code', 'QMS-36')->firstOrFail();

        $uuid = $this->withToken($token)->postJson('/api/v1/applications', [
            'form_definition_uuid' => $form->uuid,
            'project_title' => 'Bad file type',
            'project_location' => 'San Fernando, Pampanga',
        ])->assertCreated()->json('data.uuid');

        $exe = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');

        $this->withToken($token)->post(
            "/api/v1/applications/{$uuid}/documents",
            [
                'label' => 'lot_plan',
                'file' => $exe,
            ],
            ['Accept' => 'application/json'],
        )->assertStatus(422);
    }
}
