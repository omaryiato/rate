<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard\User;

class AddUser extends UserRequest
{
    public function rules(): array
    {
        return $this->userRules();
    }
}
