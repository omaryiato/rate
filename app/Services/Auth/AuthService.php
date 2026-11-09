<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Auth\AuthRepository;
use App\Services\LoggingService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly AuthRepository $repository,
        private readonly LoggingService $loggingService,
    ) {}

    /** Replace all previous tokens atomically after verifying an active account. */
    public function login(array $data): array
    {
        $result = DB::transaction(function () use ($data): array {
            $user = $this->repository->findActiveByPhoneNumber($data['phone_number']);

            if (! $user || ! $user->password || ! Hash::check($data['password'], $user->password)) {
                throw new AuthenticationException('Invalid credentials.');
            }

            $expiresAt = now()->addHours(10);
            $this->repository->deleteTokens($user);
            $token = $this->repository->createToken($user, $expiresAt);

            return [
                'user' => $user,
                'token' => $token->plainTextToken,
                'expires_at' => $expiresAt->toIso8601String(),
            ];
        });

        $this->loggingService->success('Login', (int) $result['user']->id);

        return $result;
    }

    /** Revoke credentials inside the caller-owned transaction without touching user content. */
    public function revokeTokens(User $user): void
    {
        $this->repository->deleteTokens($user);
    }

    public function logout(User $user): void
    {
        $this->repository->deleteCurrentToken($user);
        $this->loggingService->success('Logout', (int) $user->id);
    }

    public function signUp(array $data): User
    {
        // Server-owned account attributes prevent public registration as admin or staff.
        $user = $this->repository->create([
            'full_name' => $data['full_name'],
            'user_name' => $data['phone_number'],
            'phone_number' => $data['phone_number'],
            'password' => Hash::make($data['password']),
            'status' => 1,
            'type' => 0,
        ]);

        $this->loggingService->success('SignUp', (int) $user->id);

        return $user;
    }
}
