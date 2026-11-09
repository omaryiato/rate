<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || (int) $user->status !== 1 || $user->deleted_at !== null) {
            return ResponseHelper::error(
                ['ar' => 'هذا الحساب معطّل أو محذوف', 'en' => 'This account is inactive or deleted'],
                (object) ['code' => 'ACCOUNT_INACTIVE'],
                403,
            );
        }

        return $next($request);
    }
}
