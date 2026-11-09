<?php

namespace App\Http\Requests\Base;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(ApiValidationResponse::make($validator));
    }

    public function authorize()
    {
        return true;
    }
}
