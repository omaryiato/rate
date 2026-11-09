<?php

declare(strict_types=1);

namespace App\Repositories\Dashboard;

use App\Models\Place;
use Illuminate\Database\Eloquent\Collection;

class PlaceRepository
{
    public function getAll(): Collection
    {
        return Place::whereNull('deleted_at')->with(['reviews.user:id,full_name', 'category'])->orderBy('id')->get();
    }

    public function getDetails(Place $place): Place
    {
        return $place->load(['reviews.user:id,full_name', 'category']);
    }

    public function create(array $attributes): Place
    {
        return Place::create($attributes);
    }

    public function update(Place $place, array $attributes): Place
    {
        $place->update($attributes);

        return $place;
    }

    public function delete(Place $place, int $actorId): Place
    {
        $place->status = 0;
        $place->updated_by = $actorId;
        $place->deleted_at = now();
        $place->save();

        return $place;
    }

    /** Only the explicitly designated category dependents are affected. */
    public function updateByCategory(int $categoryId, array $attributes): void
    {
        Place::where('category_id', $categoryId)->whereNull('deleted_at')->update($attributes);
    }
}
