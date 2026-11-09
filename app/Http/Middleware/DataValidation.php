<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ResponseHelper;


class DataValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        try{

            $inputs = $request->all();

            foreach ($inputs as $key => $value) {

                if (is_string($value)) {

                    if (preg_match('/<script\b[^>]*>(.*?)<\/script>/is', $value)) {
                        return ResponseHelper::error(
                                    $key,
                                    [
                                        'en' => trans('validation.invalid_data'),
                                        'ar' => trans('validation.invalid_data'),
                                    ],
                                    400);
                    }

                    if (preg_match('/<iframe|<embed|<object/i', $value)) {
                        return ResponseHelper::error(
                                    $key,
                                    [
                                        'en' => trans('validation.invalid_data'),
                                        'ar' => trans('validation.invalid_data'),
                                    ],
                                    400);
                    }
                }
            }

            return $next($request);

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
