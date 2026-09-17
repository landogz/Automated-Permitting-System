<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\StoreNotificationTemplateRequest;
use App\Http\Resources\NotificationTemplateResource;
use App\Http\Responses\ApiResponse;
use App\Models\NotificationTemplate;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    /**
     * List notification templates.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('workflow.manage'), 403);

        $paginator = $this->notifications->listTemplates(
            $request->string('search')->toString(),
            (int) $request->integer('per_page', 25),
        );

        return ApiResponse::success('Notification templates retrieved', [
            'items' => NotificationTemplateResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Create a notification template.
     */
    public function store(StoreNotificationTemplateRequest $request): JsonResponse
    {
        $template = $this->notifications->createTemplate($request->validated());

        return ApiResponse::success('Notification template created', new NotificationTemplateResource($template), 201);
    }

    /**
     * Soft-delete a notification template.
     */
    public function destroy(Request $request, NotificationTemplate $notificationTemplate): JsonResponse
    {
        abort_unless($request->user()?->can('workflow.manage'), 403);
        $this->notifications->deleteTemplate($notificationTemplate);

        return ApiResponse::success('Notification template deleted');
    }
}
