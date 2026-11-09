<?php

namespace App\Repositories\Client;

use App\Models\Category;
use App\Models\Feedback;
use App\Models\Place;
use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;

class MainRepository
{
    public function getAll(): Collection
    {
        return Category::where('status', 1)
            ->whereHas('places', fn ($query) => $query->where('status', 1))
            ->with([
                'places' => fn ($query) => $query->where('status', 1)->orderBy('id'),
                'places.category',
                'places.reviews' => fn ($query) => $query->orderByDesc('id'),
                'places.reviews.user:id,full_name',
            ])
            ->orderBy('id')->get();
    }

    public function getPlaces(Category $category): Collection
    {
        return Place::where('status', 1)->where('category_id', $category->id)
            ->with([
                'category',
                'reviews' => fn ($query) => $query->orderByDesc('id'),
                'reviews.user:id,full_name',
            ])
            ->orderBy('id')->get();
    }

    public function getReviews(Place $place): Collection
    {
        return $place->reviews()->with('user:id,full_name')->orderByDesc('id')->get();
    }

    public function review(array $request)
    {
        return Review::create($request)->load('user:id,full_name');
    }

    public function feedback(array $request)
    {
        return Feedback::create($request);
    }
}
