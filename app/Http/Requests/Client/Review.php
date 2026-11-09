<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class Review extends BaseRequest
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
            'user_id' => [
                'bail',
                'required',
                'integer',
                'exists:users,id'
            ],
            'place_id' => [
                'bail',
                'required',
                'integer',
                'exists:places,id'
            ],
            'comment' => [
                'bail',
                'required_without:rating',
                'nullable',
                'string',
                'max:1000'
            ],
            'rating' => [
                'bail',
                'required_without:comment',
                'nullable',
                'integer',
                'in:1,2,3,4,5',
            ],

        ];
    }
}
