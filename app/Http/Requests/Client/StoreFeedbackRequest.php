<?php

declare(strict_types=1);

namespace App\Http\Requests\Client;

use App\Http\Requests\Base\BaseRequest;

class StoreFeedbackRequest extends BaseRequest
{
    public function rules(): array
    {
        return ['subject' => ['required', 'string', 'max:255'], 'description' => ['required', 'string', 'max:5000']];
    }
}
