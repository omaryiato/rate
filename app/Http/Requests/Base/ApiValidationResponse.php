<?php

declare(strict_types=1);

namespace App\Http\Requests\Base;

use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;

class ApiValidationResponse
{
    public static function make(Validator $validator): JsonResponse
    {
        $details = [];
        foreach ($validator->errors()->keys() as $field) {
            $rules = $validator->failed()[$field] ?? [];
            $rule = strtolower((string) array_key_first($rules));
            $parameters = reset($rules) ?: [];
            foreach (['ar', 'en'] as $locale) {
                $key = 'validation.'.last(explode('.', $field)).'.'.$rule;
                $message = trans($key, [], $locale);
                // Existing field messages sometimes hardcode a different numeric limit.
                if (in_array($rule, ['max', 'min'], true)) {
                    $message = $locale === 'ar'
                        ? ($rule === 'max' ? 'حقل :attribute يتجاوز الحد المسموح (:max).' : 'حقل :attribute أقل من الحد المسموح (:min).')
                        : ($rule === 'max' ? 'The :attribute field exceeds the allowed maximum (:max).' : 'The :attribute field is below the allowed minimum (:min).');
                }
                if (! is_string($message) || $message === $key) {
                    $message = $locale === 'ar' ? 'قيمة الحقل :attribute غير صحيحة.' : 'The :attribute field is invalid.';
                }
                $details[$field][$locale] = strtr($message, [':attribute' => $field, ':max' => (string) ($parameters[0] ?? ''), ':min' => (string) ($parameters[0] ?? '')]);
            }
        }

        return ResponseHelper::error(
            ['ar' => 'يرجى مراجعة البيانات المدخلة', 'en' => 'Please review the submitted data'],
            (object) ['code' => 'VALIDATION_ERROR', 'details' => $details],
            422,
        );
    }
}
