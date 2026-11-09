<?php

namespace App\Http\Requests\Dashboard\Category;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class AddCategory extends BaseRequest
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
            'en_name' => [
                'required',
                'string',
                'max:255',
                'unique:category,en_name',
            ],

            'ar_name' => [
                'required',
                'string',
                'max:255',
                'unique:category,ar_name',
            ],

            'sort' => ['sometimes', 'nullable', 'integer', 'min:0'],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:150',
                'unique:category,slug',
            ],

            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'dimensions:max_width=4096,max_height=4096',
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

        ];
    }
}
