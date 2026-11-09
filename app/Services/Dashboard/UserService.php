<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\User;
use App\Repositories\Dashboard\UserRepository;
use App\Services\Auth\AuthService;
use App\Services\ImageStorageService;
use App\Services\LoggingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserService
{
    private const FIELDS = ['full_name', 'user_name', 'phone_number', 'email_address', 'password', 'status', 'type'];

    public function __construct(
        private readonly UserRepository $repository,
        private readonly AuthService $authService,
        private readonly ImageStorageService $images,
        private readonly LoggingService $logging,
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getDetails(User $user): User
    {
        $this->ensurePresent($user);

        return $this->repository->getDetails($user);
    }

    public function create(array $data, int $actorId, ?UploadedFile $avatar = null): User
    {
        $attributes = $this->prepareAttributes($data);
        $attributes['status'] = $attributes['status'] ?? 1;
        $attributes['type'] = $attributes['type'] ?? 0;
        $path = $avatar ? $this->images->store($avatar, 'users') : null;
        if ($path) {
            $attributes['avatar'] = $path;
        }
        try {
            $user = $this->repository->create($attributes);
        } catch (Throwable $exception) {
            $this->images->delete($path);
            throw $exception;
        }
        $this->logging->success('CreateUser', $actorId);

        return $user;
    }

    public function update(array $data, User $user, int $actorId, ?UploadedFile $avatar = null): User
    {
        $this->ensurePresent($user);
        $attributes = $this->prepareAttributes($data);
        $oldPath = $user->avatar;
        $path = $avatar ? $this->images->store($avatar, 'users') : null;
        if ($path) {
            $attributes['avatar'] = $path;
        }
        $revokeTokens = isset($attributes['password']) || (isset($attributes['status']) && (int) $attributes['status'] === 0);
        try {
            if ($revokeTokens) {
                // The service owns both writes; token failure must roll back the user change.
                $user = DB::transaction(function () use ($user, $attributes): User {
                    $lockedUser = $this->repository->lockById((int) $user->id);
                    $this->ensurePresent($lockedUser);
                    $updatedUser = $this->repository->update($lockedUser, $attributes);
                    $this->authService->revokeTokens($updatedUser);

                    return $updatedUser;
                });
            } else {
                $user = $this->repository->update($user, $attributes);
            }
        } catch (Throwable $exception) {
            $this->images->delete($path);
            throw $exception;
        }
        if ($path) {
            $this->images->delete($oldPath);
        }
        $this->logging->success('UpdateUser', $actorId);

        return $user;
    }

    public function delete(User $user, int $actorId): User
    {
        $this->ensurePresent($user);
        $user = DB::transaction(function () use ($user): User {
            $lockedUser = $this->repository->lockById((int) $user->id);
            $this->ensurePresent($lockedUser);
            $deletedUser = $this->repository->delete($lockedUser);
            $this->authService->revokeTokens($deletedUser);

            return $deletedUser;
        });
        $this->logging->success('DeleteUser', $actorId);

        return $user;
    }

    /** Omitted fields stay untouched; an empty password never clears the existing hash. */
    private function prepareAttributes(array $data): array
    {
        $attributes = Arr::only($data, self::FIELDS);
        foreach (['status', 'type', 'password'] as $field) {
            if (array_key_exists($field, $attributes) && ($attributes[$field] === null || $attributes[$field] === '')) {
                unset($attributes[$field]);
            }
        }
        if (isset($attributes['password'])) {
            $attributes['password'] = Hash::make($attributes['password']);
        }

        return $attributes;
    }

    private function ensurePresent(User $user): void
    {
        if ($user->deleted_at !== null) {
            throw (new ModelNotFoundException)->setModel(User::class);
        }
    }
}
