<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\Category;
use App\Repositories\Dashboard\CategoryRepository;
use App\Services\ImageStorageService;
use App\Services\LoggingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class CategoryService
{
    private const FIELDS = ['en_name', 'ar_name', 'description', 'status', 'slug', 'icone', 'sort'];

    public function __construct(
        private readonly CategoryRepository $repository,
        private readonly PlaceService $placeService,
        private readonly ImageStorageService $images,
        private readonly LoggingService $logging,
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getDetails(Category $category): Category
    {
        $this->ensurePresent($category);

        return $this->repository->getDetails($category);
    }

    public function create(array $data, int $actorId, ?UploadedFile $image = null): Category
    {
        $attributes = Arr::only($data, self::FIELDS);
        $attributes['status'] = $attributes['status'] ?? 1;
        $attributes['sort'] = $attributes['sort'] ?? 0;
        $attributes['created_by'] = $actorId;
        $attributes['updated_by'] = $actorId;
        $path = $image ? $this->images->store($image, 'categories') : null;
        if ($path) {
            $attributes['image'] = $path;
        }
        try {
            $category = $this->repository->create($attributes);
        } catch (Throwable $exception) {
            $this->images->delete($path);
            throw $exception;
        }
        $this->logging->success('CreateCategory', $actorId);

        return $category;
    }

    public function update(array $data, Category $category, int $actorId, ?UploadedFile $image = null): Category
    {
        $this->ensurePresent($category);
        $attributes = Arr::only($data, self::FIELDS);
        foreach (['status', 'sort'] as $field) {
            if (array_key_exists($field, $attributes) && $attributes[$field] === null) {
                unset($attributes[$field]);
            }
        }
        $attributes['updated_by'] = $actorId;
        $oldPath = $category->image;
        $path = $image ? $this->images->store($image, 'categories') : null;
        if ($path) {
            $attributes['image'] = $path;
        }
        try {
            if (isset($attributes['status']) && (int) $attributes['status'] === 0) {
                $category = DB::transaction(function () use ($category, $attributes, $actorId): Category {
                    $this->placeService->deactivateByCategory((int) $category->id, $actorId);

                    return $this->repository->update($category, $attributes);
                });
            } else {
                $category = $this->repository->update($category, $attributes);
            }
        } catch (Throwable $exception) {
            $this->images->delete($path);
            throw $exception;
        }
        if ($path) {
            $this->images->delete($oldPath);
        }
        $this->logging->success('UpdateCategory', $actorId);

        return $category;
    }

    public function delete(Category $category, int $actorId): Category
    {
        $this->ensurePresent($category);
        $category = DB::transaction(function () use ($category, $actorId): Category {
            $this->placeService->deactivateByCategory((int) $category->id, $actorId, true);

            return $this->repository->delete($category, $actorId);
        });
        $this->logging->success('DeleteCategory', $actorId);

        return $category;
    }

    private function ensurePresent(Category $category): void
    {
        if ($category->deleted_at !== null) {
            throw (new ModelNotFoundException)->setModel(Category::class);
        }
    }
}
