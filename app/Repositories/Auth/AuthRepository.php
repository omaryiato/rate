<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

class AuthRepository
{
    /** Lock the account while replacing tokens to serialize simultaneous logins. */
    public function findActiveByPhoneNumber(string $phoneNumber): ?User
    {
        return User::where('phone_number', $phoneNumber)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->lockForUpdate()
            ->first();
    }

    public function deleteCurrentToken(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function deleteTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    public function createToken(User $user, \DateTimeInterface $expiresAt): NewAccessToken
    {
        return $user->createToken('auth_token', ['*'], $expiresAt);
    }

    /** Persist only attributes prepared by the authentication service. */
    public function create(array $attributes): User
    {
        return User::create($attributes);
    }
}
