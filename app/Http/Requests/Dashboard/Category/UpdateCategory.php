<?php

namespace App\Http\Requests\Dashboard\Category;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;


class UpdateCategory extends BaseRequest
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
        $id = $this->route("category");

        return [
            'en_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('category', 'en_name')
                        ->ignore($id)
                        ->where('id', $this->input('id')),
            ],

            'ar_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('category', 'ar_name')
                        ->ignore($id)
                        ->where('id', $this->input('id')),
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'image' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'icone' => [
                'nullable',
                'string',
                'max:150',
            ],

            'status' => [
                'nullable',
                'integer',
                'in:0,1',
            ],

            'updated_by' => [
                'required',
                'integer',
                'exists:users,id'
            ],
        ];
    }
}
