<?php

declare(strict_types=1);

namespace App\Repositories\Dashboard;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function getAll(): Collection
    {
        return User::select(['id', 'full_name', 'user_name', 'phone_number', 'email_address', 'status', 'type', 'avatar', 'created_at', 'updated_at', 'deleted_at'])
            ->whereNull('deleted_at')->with('reviews.user:id,full_name')->orderBy('id')->get();
    }

    public function getDetails(User $user): User
    {
        return $user->load('reviews.user:id,full_name');
    }

    public function create(array $attributes): User
    {
        return User::create($attributes);
    }

    public function update(User $user, array $attributes): User
    {
        $user->update($attributes);

        return $user;
    }

    /** Serialize credential changes with login's lock on the same user row. */
    public function lockById(int $id): User
    {
        return User::whereKey($id)->lockForUpdate()->firstOrFail();
    }

    /** Preserve the account row, avatar, reviews and every other dependent record. */
    public function delete(User $user): User
    {
        $user->status = 0;
        $user->deleted_at = now();
        $user->save();

        return $user;
    }
}
