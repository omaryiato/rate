<?php

declare(strict_types=1);

namespace App\Repositories\Dashboard;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    private function relationships(): array
    {
        return [
            'places' => fn ($query) => $query->whereNull('deleted_at'),
            'places.category',
            'places.reviews.user:id,full_name',
        ];
    }

    public function getAll(): Collection
    {
        return Category::whereNull('deleted_at')->with($this->relationships())->orderBy('id')->get();
    }

    public function getDetails(Category $category): Category
    {
        return $category->load($this->relationships());
    }

    public function create(array $attributes): Category
    {
        return Category::create($attributes);
    }

    public function update(Category $category, array $attributes): Category
    {
        $category->update($attributes);

        return $category;
    }

    public function delete(Category $category, int $actorId): Category
    {
        $category->status = 0;
        $category->updated_by = $actorId;
        $category->deleted_at = now();
        $category->save();

        return $category;
    }
}
