<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard\User;

use App\Http\Requests\Base\BaseRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UserRequest extends BaseRequest
{
    protected function userRules(?User $user = null): array
    {
        return [
            'full_name' => ['bail', 'required', 'string', 'max:255'],
            'user_name' => ['bail', 'required', 'string', 'max:255', Rule::unique('users', 'user_name')->ignore($user)],
            'phone_number' => ['bail', 'nullable', 'string', 'max:20', 'regex:/^\+?[0-9]{7,20}$/', Rule::unique('users', 'phone_number')->ignore($user)],
            'email_address' => ['bail', 'nullable', 'email', 'max:255', Rule::unique('users', 'email_address')->ignore($user)],
            'password' => ['bail', 'nullable', 'string', 'min:8', 'max:72'],
            'status' => ['nullable', 'integer', 'in:0,1'],
            'type' => ['nullable', 'integer', 'in:0,1,2'],
            'avatar' => ['bail', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4096,max_height=4096'],
        ];
    }

    /** Bcrypt's limit is bytes, so multibyte passwords need a separate check. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $password = $this->input('password');
            if (is_string($password) && strlen($password) > 72) {
                $validator->addFailure('password', 'Max', [72]);
            }
        });
    }
}
