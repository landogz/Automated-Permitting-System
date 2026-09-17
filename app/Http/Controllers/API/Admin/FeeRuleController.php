<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fee\StoreFeeRuleRequest;
use App\Http\Requests\Fee\UpdateFeeRuleRequest;
use App\Http\Resources\FeeRuleResource;
use App\Http\Responses\ApiResponse;
use App\Models\FeeRule;
use App\Services\Fee\FeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeRuleController extends Controller
{
    public function __construct(private readonly FeeService $fees)
    {
    }

    /**
     * List fee rules for the Order of Payment engine.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('workflow.manage'), 403);

        $paginator = $this->fees->listRules(
            $request->string('search')->toString(),
            (int) $request->integer('per_page', 25),
        );

        return ApiResponse::success('Fee rules retrieved', [
            'items' => FeeRuleResource::collection($paginator->items()),
            'summary' => $this->fees->feeRuleSummary(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Create a fee rule.
     */
    public function store(StoreFeeRuleRequest $request): JsonResponse
    {
        $rule = $this->fees->createRule($request->validated());

        return ApiResponse::success('Fee rule created', new FeeRuleResource($rule), 201);
    }

    /**
     * Update a fee rule.
     */
    public function update(UpdateFeeRuleRequest $request, FeeRule $feeRule): JsonResponse
    {
        $rule = $this->fees->updateRule($feeRule, $request->validated());

        return ApiResponse::success('Fee rule updated', new FeeRuleResource($rule));
    }

    /**
     * Soft-delete a fee rule.
     */
    public function destroy(Request $request, FeeRule $feeRule): JsonResponse
    {
        abort_unless($request->user()?->can('workflow.manage'), 403);
        $this->fees->deleteRule($feeRule);

        return ApiResponse::success('Fee rule deleted');
    }
}
