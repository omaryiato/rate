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
        $id = $this->route('category');

        return [
            'en_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('category', 'en_name')
                    ->ignore($id),
            ],

            'ar_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('category', 'ar_name')
                    ->ignore($id),
            ],

            'slug' => ['sometimes', 'nullable', 'string', 'max:150', Rule::unique('category', 'slug')->ignore($id)],
            'sort' => ['sometimes', 'nullable', 'integer', 'min:0'],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'image' => [
                'nullable',
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
