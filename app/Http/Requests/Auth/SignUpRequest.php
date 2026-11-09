<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Requests\Base\BaseRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SignUpRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{7,20}$/'],
            'password' => ['required', 'string', 'min:8', 'max:72'],
        ];
    }

    /** Bcrypt accepts at most 72 bytes, including multibyte passwords. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $password = $this->input('password');

            if (is_string($password) && strlen($password) > 72) {
                $validator->addFailure('password', 'Max', [72]);
            }
        });
    }

    /** Return bilingual validation details without changing other API requests. */
    protected function failedValidation(Validator $validator): void
    {
        $details = [];
        $messages = [
            'full_name' => [
                'required' => ['الاسم مطلوب.', 'Full name is required.'],
                'string' => ['الاسم يجب أن يكون نصًا.', 'Full name must be a string.'],
                'max' => ['الاسم يجب ألا يتجاوز 255 حرفًا.', 'Full name must not exceed 255 characters.'],
            ],
            'phone_number' => [
                'required' => ['رقم الموبايل مطلوب.', 'Phone number is required.'],
                'string' => ['رقم الموبايل يجب أن يكون نصًا.', 'Phone number must be a string.'],
                'max' => ['رقم الموبايل يجب ألا يتجاوز 20 حرفًا.', 'Phone number must not exceed 20 characters.'],
                'regex' => ['رقم الموبايل يجب أن يتكون من 7 إلى 20 رقمًا مع علامة + اختيارية في البداية.', 'Phone number must contain 7 to 20 digits with an optional leading +.'],
            ],
            'password' => [
                'required' => ['كلمة المرور مطلوبة.', 'Password is required.'],
                'string' => ['كلمة المرور يجب أن تكون نصًا.', 'Password must be a string.'],
                'min' => ['كلمة المرور يجب ألا تقل عن 8 أحرف.', 'Password must contain at least 8 characters.'],
                'max' => ['كلمة المرور يجب ألا تتجاوز 72 بايت.', 'Password must not exceed 72 bytes.'],
            ],
        ];

        foreach ($validator->failed() as $field => $rules) {
            $rule = strtolower(array_key_first($rules));
            [$ar, $en] = $messages[$field][$rule];
            $details[$field] = ['ar' => $ar, 'en' => $en];
        }

        throw new HttpResponseException(ResponseHelper::error(
            ['ar' => 'يرجى مراجعة البيانات المدخلة', 'en' => 'Please review the submitted data'],
            (object) ['code' => 'VALIDATION_ERROR', 'details' => $details],
            422,
        ));
    }
}
