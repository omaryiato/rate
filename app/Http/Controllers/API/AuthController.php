<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SignUpRequest;
use App\Http\Requests\Login\Login;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function signup(SignUpRequest $request): JsonResponse
    {
        $user = $this->authService->signUp($request->validated());

        return ResponseHelper::success(
            UserResource::make($user),
            ['ar' => 'تم إنشاء الحساب بنجاح', 'en' => 'Account created successfully'],
            201,
        );
    }

    public function login(Login $request): JsonResponse
    {
        return ResponseHelper::success(
            LoginResource::make($this->authService->login($request->validated())),
            ['ar' => 'تم تسجيل الدخول بنجاح', 'en' => 'Logged in successfully'],
            200,
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return ResponseHelper::success(null, ['ar' => 'تم تسجيل الخروج بنجاح', 'en' => 'Logged out successfully']);
    }

    public function me(Request $request): JsonResponse
    {
        return ResponseHelper::success(UserResource::make($request->user()), ['ar' => 'الجلسة صالحة', 'en' => 'Session is valid']);
    }
}
