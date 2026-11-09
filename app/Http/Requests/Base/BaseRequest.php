<?php
namespace App\Http\Requests\Base;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;

class BaseRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $field = $validator->errors()->keys()[0] ?? null;

        $rule = null;

        if ($field && isset($validator->failed()[$field])) {
            $failedRules = $validator->failed()[$field];
            $rule = strtolower(array_key_first($failedRules));
        }

        $field_name = $field ? last(explode('.', $field)) : null;

        $translation_key = $field_name && $rule
            ? 'validation.' . $field_name . '.' . $rule
            : null;

        // $translation_key = $field && $rule
        //     ? 'validation.' . $field . '.' . $rule
        //     : null;

        $response_message = [
            'en' => $translation_key ? trans($translation_key, [], 'en') : 'Validation Error',
            'ar' => $translation_key ? trans($translation_key, [], 'ar') : 'خطأ في التحقق',
        ];

        throw new HttpResponseException(
            ResponseHelper::error(
                $response_message,
                $validator->errors(),
                422
            )
        );
    }
    public function authorize()
    {
        return true;
    }
}
