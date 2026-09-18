<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\FormDefinition;
use App\Support\PermitApplication\FieldCatalog;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormDefinitionBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_list_templates_and_update_form_schema(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
            'device_name' => 'test',
        ])->assertOk()->json('data.token');

        $templates = $this->withToken($token)
            ->getJson('/api/v1/admin/form-definitions/templates')
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertNotEmpty($templates->json('data.items'));

        $form = FormDefinition::query()->where('code', 'QMS-36')->firstOrFail();

        $schema = FieldCatalog::qms36Schema();
        $schema['sections'][0]['fields'][] = [
            'name' => 'custom_plot_notes',
            'label' => 'Custom plot notes',
            'type' => 'textarea',
            'required' => false,
        ];

        $this->withToken($token)
            ->putJson("/api/v1/admin/form-definitions/{$form->uuid}", [
                'title' => $form->title,
                'revision' => '03',
                'schema' => $schema,
                'required_attachments' => ['tax_declaration', 'lot_plan'],
                'is_active' => true,
            ])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.revision', '03');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'form_definition.updated',
        ]);

        $form->refresh();
        $flat = FieldCatalog::flattenFields($form->schema);
        $this->assertTrue(collect($flat)->contains(fn ($field) => ($field['name'] ?? '') === 'custom_plot_notes'));
    }

    public function test_admin_can_save_map_location_field_type(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
            'device_name' => 'test',
        ])->assertOk()->json('data.token');

        $this->withToken($token)
            ->postJson('/api/v1/admin/form-definitions', [
                'code' => 'QMS-LOC',
                'title' => 'Location field form',
                'schema' => [
                    'sections' => [
                        [
                            'title' => 'Site',
                            'fields' => [
                                [
                                    'name' => 'owner_name',
                                    'label' => 'Owner name',
                                    'type' => 'text',
                                    'required' => true,
                                ],
                                [
                                    'name' => 'site_address',
                                    'label' => 'Site address',
                                    'type' => 'location',
                                    'required' => true,
                                ],
                            ],
                        ],
                    ],
                ],
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('status', true);

        $form = FormDefinition::query()->where('code', 'QMS-LOC')->firstOrFail();
        $flat = FieldCatalog::flattenFields($form->schema);
        $this->assertTrue(collect($flat)->contains(
            fn ($field) => ($field['name'] ?? '') === 'site_address' && ($field['type'] ?? '') === 'location'
        ));
    }

    public function test_admin_cannot_create_form_without_fields(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
            'device_name' => 'test',
        ])->assertOk()->json('data.token');

        $this->withToken($token)
            ->postJson('/api/v1/admin/form-definitions', [
                'code' => 'QMS-TEST',
                'title' => 'Empty schema form',
                'schema' => [
                    'sections' => [
                        ['title' => 'Empty', 'fields' => []],
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_inspector_cannot_manage_forms(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
            'device_name' => 'test',
        ])->assertOk()->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/form-definitions')
            ->assertForbidden();
    }
}
