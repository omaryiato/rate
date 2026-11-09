<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard\User;

class UpdateUser extends UserRequest
{
    public function rules(): array
    {
        return $this->userRules($this->route('user'));
    }
}
