<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAccessibility
{
    public function handle(Request $request, Closure $next, string $access = 'dashboard'): Response
    {
        $user = $request->user();

        if (! $user) {
            return ResponseHelper::error(['ar' => 'يلزم تسجيل الدخول', 'en' => 'Authentication required'], (object) ['code' => 'UNAUTHENTICATED'], 401);
        }

        // Staff retain normal dashboard access; user administration is restricted to admins.
        $allowedTypes = $access === 'users' ? [1] : [1, 2];
        if (! in_array((int) $user->type, $allowedTypes, true)) {
            return ResponseHelper::error(['ar' => 'لا تملك الصلاحية', 'en' => 'Access denied'], (object) ['code' => 'FORBIDDEN'], 403);
        }

        return $next($request);
    }
}
