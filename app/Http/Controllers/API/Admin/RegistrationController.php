<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registration\DeclineRegistrationRequest;
use App\Http\Resources\RegistrationResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\Registration\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function __construct(private readonly RegistrationService $service)
    {
    }

    /**
     * List applicant registrations for admin review.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('users.manage'), 403);

        $paginator = $this->service->list(
            $request->string('status', 'pending')->toString(),
            $request->string('search')->toString(),
            (int) $request->integer('per_page', 15),
        );

        return ApiResponse::success('Registrations retrieved', [
            'items' => RegistrationResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Approve a pending applicant registration.
     */
    public function approve(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()?->can('users.manage'), 403);

        $applicant = $this->service->approve($user, $request->user());

        return ApiResponse::success('Registration approved', new RegistrationResource($applicant));
    }

    /**
     * Decline a pending applicant registration with a reason.
     */
    public function decline(DeclineRegistrationRequest $request, User $user): JsonResponse
    {
        $applicant = $this->service->decline(
            $user,
            $request->user(),
            $request->string('reason')->toString(),
        );

        return ApiResponse::success('Registration declined', new RegistrationResource($applicant));
    }
}
