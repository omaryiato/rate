<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Auth;

class UserAccessibility
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return ResponseHelper::error(
                null,
                [
                    'en' => trans('validation.unauthenticated'),
                    'ar' => trans('validation.unauthenticated'),
                ],
                401
            );
        }

        if ($user->type == 0) {
            return ResponseHelper::error(
                null,
                [
                    'en' => trans('validation.unauthorized'),
                    'ar' => trans('validation.unauthorized'),
                ],
                403
            );
        }

        return $next($request);
    }
}
