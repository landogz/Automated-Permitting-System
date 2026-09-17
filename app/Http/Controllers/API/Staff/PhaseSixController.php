<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\SendNotificationRequest;
use App\Http\Requests\Records\StoreArchiveRecordRequest;
use App\Http\Requests\Records\StoreLogbookEntryRequest;
use App\Http\Resources\ArchiveRecordResource;
use App\Http\Resources\LogbookEntryResource;
use App\Http\Resources\UserNotificationResource;
use App\Http\Responses\ApiResponse;
use App\Models\UserNotification;
use App\Services\Dashboard\DashboardService;
use App\Services\Notification\NotificationService;
use App\Services\Records\RecordsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhaseSixController extends Controller
{
    public function __construct(
        private readonly RecordsService $records,
        private readonly NotificationService $notifications,
        private readonly DashboardService $dashboard,
    ) {
    }

    /**
     * Operational dashboard KPIs for admin/staff shell.
     */
    public function dashboardStats(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $user
            && (
                $user->can('applications.manage')
                || $user->can('audit.view')
                || $user->can('users.manage')
                || $user->can('evaluations.manage')
                || $user->can('compliance.manage')
                || $user->can('inspections.manage')
                || $user->can('fees.manage')
                || $user->can('records.manage')
            ),
            403
        );

        return ApiResponse::success('Dashboard stats retrieved', $this->dashboard->operationalStats());
    }

    /**
     * List logbook entries (G-01 / O-02 / E-series / G-05 / G-06).
     */
    public function listLogbook(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('records.manage'), 403);

        $paginator = $this->records->listLogbook(
            $request->string('search')->toString(),
            $request->string('book_type')->toString() ?: null,
            (int) $request->integer('per_page', 25),
        );

        return ApiResponse::success('Logbook entries retrieved', [
            'items' => LogbookEntryResource::collection($paginator->items()),
            'summary' => $this->records->logbookSummary(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Create a logbook entry.
     */
    public function storeLogbook(StoreLogbookEntryRequest $request): JsonResponse
    {
        $entry = $this->records->createLogbookEntry($request->user(), $request->validated());

        return ApiResponse::success('Logbook entry created', new LogbookEntryResource($entry), 201);
    }

    /**
     * List archive records.
     */
    public function listArchives(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('records.manage'), 403);

        $paginator = $this->records->listArchives(
            $request->string('search')->toString(),
            $request->string('media_type')->toString() ?: null,
            (int) $request->integer('per_page', 25),
        );

        return ApiResponse::success('Archive records retrieved', [
            'items' => ArchiveRecordResource::collection($paginator->items()),
            'summary' => $this->records->archiveSummary(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Create an archive / CICTO backup metadata record.
     */
    public function storeArchive(StoreArchiveRecordRequest $request): JsonResponse
    {
        $record = $this->records->createArchive($request->user(), $request->validated());

        return ApiResponse::success('Archive record created', new ArchiveRecordResource($record), 201);
    }

    /**
     * List notifications for the authenticated user.
     */
    public function myNotifications(Request $request): JsonResponse
    {
        $paginator = $this->notifications->listForUser(
            $request->user(),
            $request->boolean('unread_only'),
            (int) $request->integer('per_page', 25),
        );

        return ApiResponse::success('Notifications retrieved', [
            'items' => UserNotificationResource::collection($paginator->items()),
            'unread_count' => $this->notifications->unreadCount($request->user()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Mark one notification as read.
     */
    public function markNotificationRead(Request $request, UserNotification $notification): JsonResponse
    {
        $model = $this->notifications->markRead($request->user(), $notification);

        return ApiResponse::success('Notification marked read', new UserNotificationResource($model));
    }

    /**
     * Mark all notifications as read for the current user.
     */
    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        $count = $this->notifications->markAllRead($request->user());

        return ApiResponse::success('Notifications marked read', ['updated' => $count]);
    }

    /**
     * Send a status notification to a user (in-app and/or email).
     */
    public function sendNotification(SendNotificationRequest $request): JsonResponse
    {
        $notification = $this->notifications->sendManual($request->user(), $request->validated());

        return ApiResponse::success('Notification sent', new UserNotificationResource($notification), 201);
    }
}
