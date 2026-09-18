<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Privilege matrix: login home + allow/deny for every demo office role.
 */
class PrivilegeMatrixTest extends TestCase
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

    private function login(string $email, string $password): array
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => $password,
        ])->assertOk();

        return [
            'token' => $response->json('data.token'),
            'redirect' => $response->json('data.redirect'),
            'permissions' => $response->json('data.user.permissions') ?? [],
        ];
    }

    /**
     * @return array<string, array{password: string, redirect: string, allow: list<string>, deny: list<string>, permissions: list<string>}>
     */
    private function matrix(): array
    {
        return [
            'admin@csfp.local' => [
                'password' => 'Admin@12345',
                'redirect' => '/admin',
                'permissions' => [
                    'departments.manage',
                    'forms.manage',
                    'audit.view',
                    'users.manage',
                    'workflow.manage',
                    'evaluations.manage',
                    'inspections.manage',
                    'fees.manage',
                    'compliance.manage',
                    'records.manage',
                ],
                'allow' => [
                    '/api/v1/staff/queue',
                    '/api/v1/staff/inspections',
                    '/api/v1/staff/orders-of-payment',
                    '/api/v1/staff/compliance-notices',
                    '/api/v1/staff/logbook-entries',
                    '/api/v1/admin/users',
                    '/api/v1/admin/departments',
                    '/api/v1/admin/fee-rules',
                    '/api/v1/admin/audit-logs',
                ],
                'deny' => [],
            ],
            'evaluator@csfp.local' => [
                'password' => 'Evaluate@123',
                'redirect' => '/admin/evaluation-queue',
                'permissions' => ['evaluations.manage', 'applications.manage'],
                'allow' => ['/api/v1/staff/queue', '/api/v1/staff/applications/lookup'],
                'deny' => [
                    '/api/v1/staff/inspections',
                    '/api/v1/staff/orders-of-payment',
                    '/api/v1/staff/compliance-notices',
                    '/api/v1/staff/logbook-entries',
                    '/api/v1/admin/users',
                    '/api/v1/admin/audit-logs',
                ],
            ],
            'inspector@csfp.local' => [
                'password' => 'Inspect@123',
                'redirect' => '/admin/inspections',
                'permissions' => ['inspections.manage', 'applications.manage'],
                'allow' => ['/api/v1/staff/inspections', '/api/v1/staff/applications/lookup'],
                'deny' => [
                    '/api/v1/staff/queue',
                    '/api/v1/staff/orders-of-payment',
                    '/api/v1/staff/compliance-notices',
                    '/api/v1/staff/logbook-entries',
                    '/api/v1/admin/users',
                    '/api/v1/admin/audit-logs',
                ],
            ],
            'assessor@csfp.local' => [
                'password' => 'Assess@1234',
                'redirect' => '/admin/orders-of-payment',
                'permissions' => ['fees.manage', 'applications.manage'],
                'allow' => ['/api/v1/staff/orders-of-payment', '/api/v1/staff/applications/lookup'],
                'deny' => [
                    '/api/v1/staff/queue',
                    '/api/v1/staff/inspections',
                    '/api/v1/staff/compliance-notices',
                    '/api/v1/staff/logbook-entries',
                    '/api/v1/admin/audit-logs',
                ],
            ],
            'compliance@csfp.local' => [
                'password' => 'Comply@1234',
                'redirect' => '/admin/compliance-notices',
                'permissions' => ['compliance.manage', 'applications.manage'],
                'allow' => ['/api/v1/staff/compliance-notices', '/api/v1/staff/applications/lookup'],
                'deny' => [
                    '/api/v1/staff/queue',
                    '/api/v1/staff/inspections',
                    '/api/v1/staff/orders-of-payment',
                    '/api/v1/staff/logbook-entries',
                    '/api/v1/admin/audit-logs',
                ],
            ],
            'records@csfp.local' => [
                'password' => 'Records@123',
                'redirect' => '/admin/logbooks',
                'permissions' => ['records.manage', 'applications.manage'],
                'allow' => ['/api/v1/staff/logbook-entries', '/api/v1/staff/archive-records', '/api/v1/staff/applications/lookup'],
                'deny' => [
                    '/api/v1/staff/queue',
                    '/api/v1/staff/inspections',
                    '/api/v1/staff/orders-of-payment',
                    '/api/v1/staff/compliance-notices',
                    '/api/v1/admin/users',
                    '/api/v1/admin/audit-logs',
                ],
            ],
            'receiving@csfp.local' => [
                'password' => 'Receive@123',
                'redirect' => '/admin/evaluation-queue',
                'permissions' => ['evaluations.manage', 'applications.manage'],
                'allow' => ['/api/v1/staff/queue'],
                'deny' => [
                    '/api/v1/staff/inspections',
                    '/api/v1/staff/logbook-entries',
                    '/api/v1/admin/users',
                    '/api/v1/admin/audit-logs',
                ],
            ],
            'official@csfp.local' => [
                'password' => 'Official@123',
                'redirect' => '/admin',
                'permissions' => [
                    'users.manage',
                    'workflow.manage',
                    'evaluations.manage',
                    'inspections.manage',
                    'fees.manage',
                    'compliance.manage',
                    'records.manage',
                ],
                'allow' => [
                    '/api/v1/staff/queue',
                    '/api/v1/staff/inspections',
                    '/api/v1/staff/orders-of-payment',
                    '/api/v1/staff/compliance-notices',
                    '/api/v1/staff/logbook-entries',
                    '/api/v1/admin/registrations',
                ],
                'deny' => [
                    '/api/v1/admin/departments',
                    '/api/v1/admin/audit-logs',
                ],
            ],
            'applicant@csfp.local' => [
                'password' => 'Applicant@123',
                'redirect' => '/applications',
                'permissions' => [],
                'allow' => ['/api/v1/applications'],
                'deny' => [
                    '/api/v1/staff/queue',
                    '/api/v1/staff/inspections',
                    '/api/v1/staff/applications/lookup',
                    '/api/v1/admin/users',
                    '/api/v1/admin/audit-logs',
                ],
            ],
        ];
    }

    public function test_privilege_matrix_for_all_demo_roles(): void
    {
        foreach ($this->matrix() as $email => $expectation) {
            $session = $this->login($email, $expectation['password']);

            $this->assertSame(
                $expectation['redirect'],
                $session['redirect'],
                "Unexpected redirect for {$email}",
            );

            foreach ($expectation['permissions'] as $permission) {
                $this->assertContains(
                    $permission,
                    $session['permissions'],
                    "Missing permission {$permission} for {$email}",
                );
            }

            if ($email !== 'admin@csfp.local') {
                $this->assertNotContains(
                    'audit.view',
                    $session['permissions'],
                    "audit.view must be admin-only; unexpected for {$email}",
                );
            }

            foreach ($expectation['allow'] as $path) {
                $response = $this->actingAsApiToken($session['token'])->getJson($path);
                $this->assertTrue(
                    $response->isSuccessful(),
                    "Expected allow for {$email} on {$path}, got {$response->status()}",
                );
            }

            foreach ($expectation['deny'] as $path) {
                $response = $this->actingAsApiToken($session['token'])->getJson($path);
                $this->assertSame(
                    403,
                    $response->status(),
                    "Expected deny for {$email} on {$path}, got {$response->status()}",
                );
            }
        }
    }
}
