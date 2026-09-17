<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Routing\StoreRoutingTemplateRequest;
use App\Http\Requests\Routing\UpdateRoutingTemplateRequest;
use App\Http\Resources\RoutingTemplateResource;
use App\Http\Responses\ApiResponse;
use App\Models\RoutingTemplate;
use App\Services\Routing\RoutingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoutingTemplateController extends Controller
{
    public function __construct(private readonly RoutingService $service)
    {
    }

    /**
     * List routing templates.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('workflow.manage'), 403);

        $paginator = $this->service->listTemplates(
            $request->string('search')->toString(),
            (int) $request->integer('per_page', 25),
        );

        return ApiResponse::success('Routing templates retrieved', [
            'items' => RoutingTemplateResource::collection($paginator->items()),
            'summary' => $this->service->templateSummary(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Create a routing template with ordered department steps.
     */
    public function store(StoreRoutingTemplateRequest $request): JsonResponse
    {
        $template = $this->service->createTemplate($request->validated());

        return ApiResponse::success('Routing template created', new RoutingTemplateResource($template), 201);
    }

    /**
     * Update a routing template and optionally replace its steps.
     */
    public function update(UpdateRoutingTemplateRequest $request, RoutingTemplate $routingTemplate): JsonResponse
    {
        $template = $this->service->updateTemplate($routingTemplate, $request->validated());

        return ApiResponse::success('Routing template updated', new RoutingTemplateResource($template));
    }

    /**
     * Soft-delete a routing template.
     */
    public function destroy(Request $request, RoutingTemplate $routingTemplate): JsonResponse
    {
        abort_unless($request->user()?->can('workflow.manage'), 403);
        $this->service->deleteTemplate($routingTemplate);

        return ApiResponse::success('Routing template deleted');
    }
}
