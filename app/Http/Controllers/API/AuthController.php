<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Login\Login;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function login(Login $request)
    {
        $user_details = User::where('email', $request->email)
            ->where('is_active', 1)
            ->first();

        if (!$user_details) {
            return ResponseHelper::error(
                null,
                [
                    'en' => trans('validation.invalid_credentials'),
                    'ar' => trans('validation.invalid_credentials'),
                ],
                401
            );
        }

        if (!Hash::check($request->password, $user_details->password)) {
            return ResponseHelper::error(
                null,
                [
                    'en' => trans('validation.invalid_credentials'),
                    'ar' => trans('validation.invalid_credentials'),
                ],
                401
            );
        }

        // Delete all previous tokens
        $user_details->tokens()->delete();

        // Create new token
        $tokenResult = $user_details->createToken('auth_token');

        $tokenResult->accessToken->expires_at = now()->addHours(10);
        $tokenResult->accessToken->save();

        $token = $tokenResult->plainTextToken;

        // $tokenResult = $user_details->createToken('auth_token');

        // $tokenResult->accessToken->expires_at = now()->addHours(10);
        // $tokenResult->accessToken->save();

        // $token = $tokenResult->plainTextToken;

        return ResponseHelper::success(
            $user_details,
            [
                'en' => trans('validation.logged_in'),
                'ar' => trans('validation.logged_in'),
                'token' => $token,
                'expires_at' => $tokenResult->accessToken->expires_at,
            ],
            200
        );
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return ResponseHelper::success(
                null,
                [
                    'en' => trans('validation.logged_out'),
                    'ar' => trans('validation.logged_out'),
                ],
                200
            );
    }

    // public function refresh(Request $request)
    // {
    //     return ResponseHelper::success(
    //             $request->user(),
    //             [
    //                 'en' => trans('validation.token_available'),
    //                 'ar' => trans('validation.token_available'),
    //             ],
    //             200
    //         );
    // }



    public function refresh(Request $request)
    {
        $user = $request->user();

        // No user (no token or invalid token)
        if (!$user) {
            return ResponseHelper::error(
                null,
                [
                    'en' => 'Unauthenticated or token missing.',
                    'ar' => 'غير مصرح أو التوكن غير موجود.',
                ],
                401
            );
        }

        // Optional: check if token exists in DB (Sanctum)
        $token = $user->currentAccessToken();

        if (!$token) {
            return ResponseHelper::error(
                null,
                [
                    'en' => 'Token not found or expired.',
                    'ar' => 'التوكن غير موجود أو منتهي.',
                ],
                401
            );
        }

        return ResponseHelper::success(
            $user,
            [
                'en' => trans('validation.token_available'),
                'ar' => trans('validation.token_available'),
            ],
            200
        );
    }


}
