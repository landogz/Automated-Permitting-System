<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\DemoLoginRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\RegistrationResource;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Services\Auth\AuthService;
use App\Services\Registration\RegistrationService;
use App\Support\OfficeHome;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly RegistrationService $registrationService,
    ) {
    }

    /**
     * Authenticate a user and issue a Sanctum API token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
            $request->string('device_name', 'api')->toString(),
        );

        return ApiResponse::success('Login successful', [
            'token' => $result['token'],
            'token_type' => 'Bearer',
            'user' => new UserResource($result['user']->load('department')),
            'redirect' => OfficeHome::pathFor($result['user']),
        ]);
    }

    /**
     * Local-only quick login (no password in the browser DOM).
     */
    public function demoLogin(DemoLoginRequest $request): JsonResponse
    {
        $result = $this->authService->demoLogin(
            $request->string('email')->toString(),
            $request->string('device_name', 'web-demo')->toString(),
        );

        $configured = collect(config('apics_demo_users.users', []))
            ->firstWhere('email', $request->string('email')->toString())['redirect'] ?? null;

        return ApiResponse::success('Login successful', [
            'token' => $result['token'],
            'token_type' => 'Bearer',
            'user' => new UserResource($result['user']->load('department')),
            'redirect' => $configured ?: OfficeHome::pathFor($result['user']),
        ]);
    }

    /**
     * Self-register an applicant account (pending admin approval).
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->registrationService->register($request->validated());

        return ApiResponse::success(
            'Registration submitted. Please wait for admin approval before signing in.',
            new RegistrationResource($user),
            201,
        );
    }

    /**
     * Revoke the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return ApiResponse::success('Logged out successfully');
    }

    /**
     * Return the authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success('Authenticated user', new UserResource(
            $request->user()->load('department')
        ));
    }
}
