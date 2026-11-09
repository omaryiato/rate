<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Helpers\ResponseHelper;
use App\Http\Requests\Base\ApiValidationResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ApiExceptionRenderer
{
    public static function render(Throwable $exception, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*') && ! $request->expectsJson()) {
            return null;
        }
        if ($exception instanceof HttpResponseException) {
            return $exception->getResponse();
        }

        if ($exception instanceof ValidationException) {
            return ApiValidationResponse::make($exception->validator);
        }

        [$status, $code, $message] = match (true) {
            $exception instanceof AuthenticationException => [401, 'UNAUTHENTICATED', ['ar' => 'يلزم تسجيل الدخول أو انتهت صلاحية الجلسة', 'en' => 'Authentication required or token expired']],
            $exception instanceof AuthorizationException => [403, 'FORBIDDEN', ['ar' => 'لا تملك صلاحية تنفيذ هذه العملية', 'en' => 'Access denied']],
            $exception instanceof ModelNotFoundException => [404, 'NOT_FOUND', ['ar' => 'البيانات المطلوبة غير موجودة', 'en' => 'Resource not found']],
            $exception instanceof UniqueConstraintViolationException => [409, 'CONFLICT', ['ar' => 'توجد بيانات مسجلة مسبقًا بالقيم نفسها', 'en' => 'A record with these unique values already exists']],
            $exception instanceof HttpExceptionInterface => [$exception->getStatusCode(), 'HTTP_ERROR', ['ar' => 'تعذر إتمام الطلب', 'en' => 'The request could not be completed']],
            default => [500, 'INTERNAL_ERROR', ['ar' => 'حدث خطأ، يرجى المحاولة لاحقًا', 'en' => 'Something went wrong. Please try again later']],
        };
        if ($exception instanceof AuthenticationException && $request->routeIs('*.login')) {
            $code = 'INVALID_CREDENTIALS';
            $message = ['ar' => 'رقم الموبايل أو كلمة المرور غير صحيحة', 'en' => 'Invalid phone number or password'];
        }
        if ($exception instanceof UniqueConstraintViolationException && $request->routeIs('*.signup')) {
            $code = 'ACCOUNT_ALREADY_EXISTS';
        }
        if ($status === 404) {
            $code = 'NOT_FOUND';
        } elseif ($status === 405) {
            $code = 'METHOD_NOT_ALLOWED';
        } elseif ($status === 429) {
            $code = 'RATE_LIMIT_EXCEEDED';
        }
        if ($status >= 500) {
            $code = 'INTERNAL_ERROR';
            $message = ['ar' => 'حدث خطأ، يرجى المحاولة لاحقًا', 'en' => 'Something went wrong. Please try again later'];
        }
        $response = ResponseHelper::error($message, (object) ['code' => $code], $status);
        if ($exception instanceof HttpExceptionInterface) {
            $response->headers->add($exception->getHeaders());
        }

        return $response;
    }
}
