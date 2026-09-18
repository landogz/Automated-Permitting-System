<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\ProjectPlan\ProjectPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectPlanController extends Controller
{
    public function __construct(private readonly ProjectPlanService $service)
    {
    }

    /**
     * Return APICS Phase I project plan progress for the dedicated admin page.
     */
    public function show(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('audit.view') || $request->user()?->hasRole('admin'), 403);

        return ApiResponse::success('Project plan retrieved', $this->service->summary());
    }
}
