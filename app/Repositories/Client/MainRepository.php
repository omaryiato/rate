<?php

namespace App\Repositories\Client;

use App\Models\Category;
use App\Models\Feedback;
use App\Models\Place;
use App\Models\Review;

class MainRepository
{
    public function getAll()
    {
        return Category::where('status', 1)
                ->whereHas('places', function ($query) {
                    $query->where('status', 1);
                })
                ->with([
                    'places' => function ($query) {
                        $query->where('status', 1);
                    },
                    'places.reviews',
                    'places.reviews.user',
                ])
                ->get();
    }

    public function getPlaces(Category $category)
    {
        return Place::where('status', 1)
                ->with([
                    'places.reviews',
                    'places.reviews.user',
                ])
                ->where('category_id', $category->id)
                ->get();
    }

    public function review(array $request)
    {
        return Review::create($request);
    }

    public function feedback(array $request)
    {
        return Feedback::create($request);
    }


}
