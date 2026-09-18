<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Http\Responses\ApiResponse;
use App\Models\AuditLog;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogs)
    {
    }

    /**
     * List audit log events for the admin audit module (filterable).
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $paginator = $this->auditLogs->paginate([
            'search' => $request->string('search')->toString(),
            'category' => $request->string('category')->toString(),
            'actor' => $request->string('actor')->toString(),
            'severity' => $request->string('severity')->toString(),
            'date_from' => $request->string('date_from')->toString(),
            'date_to' => $request->string('date_to')->toString(),
            'range' => $request->string('range')->toString(),
            'per_page' => (int) $request->integer('per_page', 50),
        ]);

        return ApiResponse::success('Audit logs retrieved', [
            'items' => AuditLogResource::collection($paginator->items()),
            'summary' => $this->auditLogs->summaryStats(),
            'filters' => $this->auditLogs->filterOptions(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * KPI strip for the audit trail console.
     */
    public function summary(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        return ApiResponse::success('Audit summary retrieved', $this->auditLogs->summaryStats());
    }

    /**
     * Show a single audit log entry with full inspect payload.
     */
    public function show(Request $request, AuditLog $auditLog): JsonResponse
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $auditLog->load(['user.roles:id,name']);

        return ApiResponse::success('Audit log retrieved', new AuditLogResource($auditLog));
    }
}
