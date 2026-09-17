<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermitApplication\StorePermitApplicationRequest;
use App\Http\Requests\PermitApplication\UpdatePermitApplicationRequest;
use App\Http\Requests\PermitApplication\UploadApplicationDocumentRequest;
use App\Http\Resources\ApplicationDocumentResource;
use App\Http\Resources\FormDefinitionResource;
use App\Http\Resources\PermitApplicationResource;
use App\Http\Responses\ApiResponse;
use App\Models\ApplicationDocument;
use App\Models\PermitApplication;
use App\Services\FormDefinition\FormDefinitionService;
use App\Services\PermitApplication\PermitApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PermitApplicationController extends Controller
{
    public function __construct(
        private readonly PermitApplicationService $service,
        private readonly FormDefinitionService $forms,
    ) {
    }

    /**
     * List the authenticated user's permit applications.
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service->listForUser(
            $request->user(),
            $request->string('search')->toString(),
            (int) $request->integer('per_page', 15),
        );

        return ApiResponse::success('Applications retrieved', [
            'items' => PermitApplicationResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * List active forms available for new applications.
     */
    public function forms(): JsonResponse
    {
        return ApiResponse::success(
            'Active forms retrieved',
            FormDefinitionResource::collection($this->forms->activeList())
        );
    }

    /**
     * Create a draft permit application.
     */
    public function store(StorePermitApplicationRequest $request): JsonResponse
    {
        $application = $this->service->createDraft($request->user(), $request->validated());

        return ApiResponse::success('Draft application created', new PermitApplicationResource($application), 201);
    }

    /**
     * Show one owned application.
     */
    public function show(Request $request, string $application): JsonResponse
    {
        try {
            $model = $this->service->showForUser($application, $request->user());
        } catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(), $e->errors(), 404);
        }

        return ApiResponse::success('Application retrieved', new PermitApplicationResource($model));
    }

    /**
     * Update a draft application.
     */
    public function update(UpdatePermitApplicationRequest $request, PermitApplication $application): JsonResponse
    {
        $model = $this->service->updateDraft($application, $request->user(), $request->validated());

        return ApiResponse::success('Application updated', new PermitApplicationResource($model));
    }

    /**
     * Upload (or replace) a labeled supporting document on a draft application.
     */
    public function uploadDocument(
        UploadApplicationDocumentRequest $request,
        PermitApplication $application,
    ): JsonResponse {
        $document = $this->service->uploadDocument(
            $application,
            $request->user(),
            (string) $request->validated('label'),
            $request->file('file'),
        );

        return ApiResponse::success(
            'Document uploaded',
            new ApplicationDocumentResource($document),
            201,
        );
    }

    /**
     * Remove a supporting document from a draft application.
     */
    public function destroyDocument(
        Request $request,
        PermitApplication $application,
        ApplicationDocument $document,
    ): JsonResponse {
        $this->service->deleteDocument($application, $document, $request->user());

        return ApiResponse::success('Document deleted');
    }

    /**
     * Stream a document for inline preview (PDF/image) or download.
     */
    public function streamDocument(
        Request $request,
        PermitApplication $application,
        ApplicationDocument $document,
    ) {
        return $this->service->streamDocument(
            $application,
            $document,
            $request->user(),
            $request->boolean('download'),
        );
    }

    /**
     * Submit a draft application for intake.
     */
    public function submit(Request $request, PermitApplication $application): JsonResponse
    {
        $model = $this->service->submit($application, $request->user());

        return ApiResponse::success('Application submitted', new PermitApplicationResource($model));
    }
}
