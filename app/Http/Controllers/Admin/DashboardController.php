<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProjectPlan\ProjectPlanService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly ProjectPlanService $projectPlan)
    {
    }

    /**
     * Admin dashboard with embedded project plan progress.
     */
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'projectPlan' => $this->projectPlan->summary(),
        ]);
    }
}
