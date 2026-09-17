<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ProjectPlan\ProjectPlanService;
use Tests\TestCase;

class ProjectPlanServiceTest extends TestCase
{
    public function test_summary_shows_all_delivery_phases_zero_through_eight(): void
    {
        $summary = app(ProjectPlanService::class)->summary();

        $this->assertSame('.cursor/plans/apics_phase_i_plan_5d06a183.plan.md', $summary['source']);
        $this->assertCount(9, $summary['phases']);
        $this->assertSame(9, $summary['summary']['total']);

        $numbers = array_column($summary['phases'], 'number');
        $this->assertSame(range(0, 8), $numbers);

        $this->assertSame('completed', $summary['phases'][0]['status']);
        $this->assertSame('completed', $summary['phases'][2]['status']);
        $this->assertSame('completed', $summary['phases'][3]['status']);
        $this->assertStringContainsString('Phase 4', $summary['phases'][4]['title']);
        $this->assertSame('completed', $summary['phases'][4]['status']);
        $this->assertStringContainsString('Phase 5', $summary['phases'][5]['title']);
        $this->assertSame('completed', $summary['phases'][5]['status']);
        $this->assertStringContainsString('Phase 6', $summary['phases'][6]['title']);
        $this->assertSame('completed', $summary['phases'][6]['status']);
        $this->assertStringContainsString('Phase 7', $summary['phases'][7]['title']);
        $this->assertSame('in_progress', $summary['phases'][7]['status']);

        $this->assertNotEmpty($summary['roadmap']);
        $this->assertSame('phase-ii', $summary['roadmap'][0]['id']);
    }
}
