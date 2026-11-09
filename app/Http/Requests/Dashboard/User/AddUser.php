<?php

namespace App\Http\Requests\Dashboard\User;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;


class AddUser extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => [
                'required',
                'string',
                'max:255',
            ],

            'user_name' => [
                'required',
                'string',
                'max:255',
                'unique:users,user_name',
            ],

            'phone_number' => [
                'nullable',
                'string',
                'max:20',
            ],

            'email_address' => [
                'nullable',
                'email',
                'max:255',
                'unique:users,email_address',
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:255',
            ],

            'status' => [
                'nullable',
                'integer',
                'in:0,1',
            ],

            'type' => [
                'nullable',
                'integer',
                'in:0,1,2',
            ],

            'created_by' => [
                'required',
                'integer',
                'exists:users,id'
            ],

            'updated_by' => [
                'required',
                'integer',
                'exists:users,id'
            ],
        ];
    }
}
