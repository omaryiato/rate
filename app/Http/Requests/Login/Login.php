<?php

declare(strict_types=1);

namespace App\Http\Requests\Login;

use App\Helpers\ResponseHelper;
use App\Http\Requests\Base\BaseRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class Login extends BaseRequest
{
    public function rules(): array
    {
        // Credential existence belongs to authentication, never input validation.
        return [
            'phone_number' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{7,20}$/'],
            'password' => ['required', 'string', 'max:72'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $password = $this->input('password');

            if (is_string($password) && strlen($password) > 72) {
                $validator->addFailure('password', 'Max', [72]);
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        $details = [];
        foreach ($validator->failed() as $field => $rules) {
            $required = array_key_exists('Required', $rules);
            $details[$field] = match ($field) {
                'phone_number' => [
                    'ar' => $required ? 'رقم الموبايل مطلوب.' : 'رقم الموبايل يجب أن يكون نصًا من 7 إلى 20 رقمًا مع علامة + اختيارية في البداية.',
                    'en' => $required ? 'Phone number is required.' : 'Phone number must be a string of 7 to 20 digits with an optional leading +.',
                ],
                'password' => [
                    'ar' => $required ? 'كلمة المرور مطلوبة.' : 'كلمة المرور يجب أن تكون نصًا لا يتجاوز 72 بايت.',
                    'en' => $required ? 'Password is required.' : 'Password must be a string of at most 72 bytes.',
                ],
            };
        }

        throw new HttpResponseException(ResponseHelper::error(
            ['ar' => 'يرجى مراجعة البيانات المدخلة', 'en' => 'Please review the submitted data'],
            (object) ['code' => 'VALIDATION_ERROR', 'details' => $details],
            422,
        ));
    }
}
