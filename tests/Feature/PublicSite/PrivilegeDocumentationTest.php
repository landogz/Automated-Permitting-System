<?php

declare(strict_types=1);

namespace Tests\Feature\PublicSite;

use Tests\TestCase;

class PrivilegeDocumentationTest extends TestCase
{
    public function test_english_privilege_documentation_is_public(): void
    {
        $this->get('/documentation/privileges/en')
            ->assertOk()
            ->assertSee('Privilege', false)
            ->assertSee('role documentation', false)
            ->assertSee('applications.manage', false)
            ->assertSee('evaluations.manage', false)
            ->assertSee('Switch to Tagalog', false)
            ->assertSee('data-privilege-flowchart', false)
            ->assertSee('privilege-flow-modal', false)
            ->assertSee('Applicant portal', false);
    }

    public function test_tagalog_privilege_documentation_is_public(): void
    {
        $this->get('/documentation/privileges/tl')
            ->assertOk()
            ->assertSee('Dokumentasyon ng mga pribilehiyo at tungkulin', false)
            ->assertSee('applications.manage', false)
            ->assertSee('Pamamahala ng evaluation', false)
            ->assertSee('Bagpalit sa English', false);
    }

    public function test_privileges_index_redirects_to_english(): void
    {
        $this->get('/documentation/privileges')
            ->assertRedirect('/documentation/privileges/en');
    }

    public function test_invalid_locale_redirects_to_english(): void
    {
        $this->get('/documentation/privileges/fr')
            ->assertNotFound();
    }
}
