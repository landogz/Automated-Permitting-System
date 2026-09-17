<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classifier\StoreClassificationRuleRequest;
use App\Http\Requests\Classifier\UpdateClassificationRuleRequest;
use App\Http\Resources\ClassificationRuleResource;
use App\Http\Responses\ApiResponse;
use App\Models\ClassificationRule;
use App\Services\Classifier\ClassifierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassificationRuleController extends Controller
{
    public function __construct(private readonly ClassifierService $service)
    {
    }

    /**
     * List classification rules for admin workflow config.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('workflow.manage'), 403);

        $paginator = $this->service->listRules(
            $request->string('search')->toString(),
            (int) $request->integer('per_page', 25),
        );

        return ApiResponse::success('Classification rules retrieved', [
            'items' => ClassificationRuleResource::collection($paginator->items()),
            'summary' => $this->service->ruleSummary(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Create a classification rule.
     */
    public function store(StoreClassificationRuleRequest $request): JsonResponse
    {
        $rule = $this->service->createRule($request->validated());

        return ApiResponse::success('Classification rule created', new ClassificationRuleResource($rule), 201);
    }

    /**
     * Update a classification rule.
     */
    public function update(UpdateClassificationRuleRequest $request, ClassificationRule $classificationRule): JsonResponse
    {
        $rule = $this->service->updateRule($classificationRule, $request->validated());

        return ApiResponse::success('Classification rule updated', new ClassificationRuleResource($rule));
    }

    /**
     * Soft-delete a classification rule.
     */
    public function destroy(Request $request, ClassificationRule $classificationRule): JsonResponse
    {
        abort_unless($request->user()?->can('workflow.manage'), 403);
        $this->service->deleteRule($classificationRule);

        return ApiResponse::success('Classification rule deleted');
    }
}
