<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserManagement\StoreUserRequest;
use App\Http\Requests\UserManagement\UpdateUserRequest;
use App\Http\Resources\ManagedUserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\UserManagement\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserManagementService $service)
    {
    }

    /**
     * Paginate users for the admin Users & Roles DataTable.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('users.manage'), 403);

        $paginator = $this->service->list([
            'search' => $request->string('search')->toString(),
            'role' => $request->string('role')->toString(),
            'is_active' => $request->string('is_active')->toString(),
            'approval_status' => $request->string('approval_status')->toString(),
        ], (int) $request->integer('per_page', 100));

        return ApiResponse::success('Users retrieved', [
            'items' => ManagedUserResource::collection($paginator->items()),
            'summary' => $this->service->summary(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Roles and departments for create/edit forms.
     */
    public function meta(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('users.manage'), 403);

        return ApiResponse::success('User form meta retrieved', $this->service->formMeta($request->user()));
    }

    /**
     * Create a staff account (office role; auto-approved).
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->service->create($request->validated(), $request->user());

        return ApiResponse::success('User created', new ManagedUserResource($user), 201);
    }

    /**
     * Show one managed user by UUID.
     */
    public function show(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()?->can('users.manage'), 403);

        $user->load(['department', 'roles']);

        return ApiResponse::success('User retrieved', new ManagedUserResource($user));
    }

    /**
     * Update user profile, role, department, or active flag.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->service->update($user, $request->validated(), $request->user());

        return ApiResponse::success('User updated', new ManagedUserResource($user));
    }

    /**
     * Soft-deactivate a user (is_active = false).
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()?->can('users.manage'), 403);

        $user = $this->service->deactivate($user, $request->user());

        return ApiResponse::success('User deactivated', new ManagedUserResource($user));
    }
}
