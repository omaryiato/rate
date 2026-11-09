<?php

namespace App\Http\Requests\Dashboard\Place;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;



class UpdatePlace extends BaseRequest
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
        $id = $this->route("place");

        return [

            'category_id' => [
                'required',
                'integer',
                'exists:category,id',
            ],

            // Product
            'en_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('places', 'en_name')
                        ->where('category_id', $this->input('category_id'))
                        ->ignore($id)
                        ->where('id', $this->input('id')),
            ],

            'ar_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('places', 'ar_name')
                        ->where('category_id', $this->input('category_id'))
                        ->ignore($id)
                        ->where('id', $this->input('id')),
            ],

            'en_description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'ar_description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:250',
            ],

            'website_url' => [
                'nullable',
                'string',
                'max:250',
            ],

            'status' => [
                'nullable',
                'integer',
                'in:0,1',
            ],

            'image' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'updated_by' => [
                'required',
                'integer',
                'exists:users,id'
            ],

            // 'product_images.*.is_primary' => [
            //     'nullable',
            //     'integer',
            //     'in:0,1',
            // ],

            // 'product_images.*.sort_order' => [
            //     'nullable',
            //     'integer',
            //     'min:0',
            // ],


            // // Variants
            // 'product_variants' => [
            //     'nullable',
            //     'array',
            // ],

            // 'product_variants.*.color_id' => [
            //     'nullable',
            //     'integer',
            //     'exists:lookup_values,id',
            // ],

            // 'product_variants.*.size_id' => [
            //     'nullable',
            //     'integer',
            //     'exists:lookup_values,id',
            // ],

            // 'product_variants.*.material_id' => [
            //     'nullable',
            //     'integer',
            //     'exists:lookup_values,id',
            // ],

            // 'product_variants.*.stone_id' => [
            //     'nullable',
            //     'integer',
            //     'exists:lookup_values,id',
            // ],

            // 'product_variants.*.length' => [
            //     'nullable',
            //     'numeric',
            // ],

            // 'product_variants.*.sku' => [
            //     'nullable',
            //     'string',
            //     'max:100',
            //     'distinct',
            //     'unique:product_variants,sku',
            //     // Rule::unique('product_variants', 'sku')
            //     //         ->ignore($id)
            //     //         ->where('id', $this->input('id')),
            // ],

            // 'product_variants.*.stock' => [
            //     'required',
            //     'integer',
            //     'min:0',
            // ],

            // 'product_variants.*.price' => [
            //     'nullable',
            //     'numeric',
            //     'min:0',
            // ],

            // 'product_variants.*.status' => [
            //     'nullable',
            //     'integer',
            //     'in:0,1',
            // ],


        ];
    }

}
