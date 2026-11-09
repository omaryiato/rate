<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseHelper;



class ApiAuditLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        try{
                    return $next($request);

            $start = microtime(true);

            $response = $next($request);

            $executionTime = (microtime(true) - $start) * 1000;

            DB::table('AUDIT_LOGS')->insert([
                'EMPLOYEE_NUMBER'        => $request->login_user ?? $request->employee_number ?? null,
                'ENDPOINT'       => $request->path(),
                'HTTP_METHOD'    => $request->method(),
                'REQUEST_BODY'   => json_encode($request->all()),
                'RESPONSE_CODE'  => $response->getStatusCode(),
                'STATUS'         => $response->getStatusCode() < 400 ? 'SUCCESS' : 'FAILED',
                'IP_ADDRESS'     => $request->getClientIp(),
                'USER_AGENT'     => $request->userAgent(),
                'EXECUTION_TIME' => $executionTime,
                'FAILURE_REASON' => json_encode([
                    'message' => $response->getContent() ?? null,
                    'error_code' => $response->getStatusCode(),
                ], JSON_UNESCAPED_UNICODE),
            ]);

        return $response;

        } catch (\Exception $exception) {
            return ResponseHelper::error(
                [
                    'en' => trans('validation.exception_error'),
                    'ar' => trans('validation.exception_error'),
                ],
                $exception->getMessage(),
                500);
        }

    }
}
