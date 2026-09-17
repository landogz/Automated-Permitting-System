<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FormDefinition\StoreFormDefinitionRequest;
use App\Http\Requests\FormDefinition\UpdateFormDefinitionRequest;
use App\Http\Resources\FormDefinitionResource;
use App\Http\Responses\ApiResponse;
use App\Models\FormDefinition;
use App\Services\FormDefinition\FormDefinitionService;
use App\Support\PermitApplication\FieldCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormDefinitionController extends Controller
{
    public function __construct(private readonly FormDefinitionService $service)
    {
    }

    /**
     * Starter field templates (QMS-36 / QMS-37) for the form builder.
     */
    public function templates(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('forms.manage'), 403);

        return ApiResponse::success('Form templates retrieved', [
            'items' => FieldCatalog::templates(),
        ]);
    }

    /**
     * Paginate form definitions.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('forms.manage'), 403);

        $paginator = $this->service->list(
            $request->string('search')->toString(),
            (int) $request->integer('per_page', 15),
        );

        return ApiResponse::success('Form definitions retrieved', [
            'items' => FormDefinitionResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Create a form definition.
     */
    public function store(StoreFormDefinitionRequest $request): JsonResponse
    {
        $form = $this->service->create($request->validated());

        return ApiResponse::success('Form definition created', new FormDefinitionResource($form), 201);
    }

    /**
     * Show a form definition.
     */
    public function show(FormDefinition $formDefinition): JsonResponse
    {
        abort_unless(request()->user()?->can('forms.manage'), 403);

        return ApiResponse::success('Form definition retrieved', new FormDefinitionResource($formDefinition));
    }

    /**
     * Update a form definition.
     */
    public function update(UpdateFormDefinitionRequest $request, FormDefinition $formDefinition): JsonResponse
    {
        $form = $this->service->update($formDefinition, $request->validated());

        return ApiResponse::success('Form definition updated', new FormDefinitionResource($form));
    }

    /**
     * Soft-delete a form definition.
     */
    public function destroy(FormDefinition $formDefinition): JsonResponse
    {
        abort_unless(request()->user()?->can('forms.manage'), 403);
        $this->service->delete($formDefinition);

        return ApiResponse::success('Form definition deleted');
    }
}
