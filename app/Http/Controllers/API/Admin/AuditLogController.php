<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Http\Responses\ApiResponse;
use App\Models\AuditLog;
use App\Repositories\AuditLogRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogRepository $repository)
    {
    }

    /**
     * List audit log events for the admin audit module.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $paginator = $this->repository->paginate(
            $request->string('search')->toString(),
            (int) $request->integer('per_page', 20),
        );

        return ApiResponse::success('Audit logs retrieved', [
            'items' => AuditLogResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Show a single audit log entry.
     */
    public function show(Request $request, AuditLog $auditLog): JsonResponse
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        return ApiResponse::success('Audit log retrieved', new AuditLogResource($auditLog->load('user')));
    }
}
