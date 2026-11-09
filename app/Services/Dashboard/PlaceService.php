<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\Place;
use App\Repositories\Dashboard\PlaceRepository;
use App\Services\ImageStorageService;
use App\Services\LoggingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Throwable;

class PlaceService
{
    private const FIELDS = ['category_id', 'en_name', 'ar_name', 'en_description', 'ar_description', 'slug', 'website_url', 'status'];

    public function __construct(
        private readonly PlaceRepository $repository,
        private readonly ImageStorageService $images,
        private readonly LoggingService $logging,
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getDetails(Place $place): Place
    {
        $this->ensurePresent($place);

        return $this->repository->getDetails($place);
    }

    public function create(array $data, int $actorId, ?UploadedFile $image = null): Place
    {
        $attributes = Arr::only($data, self::FIELDS);
        $attributes['status'] = $attributes['status'] ?? 1;
        $attributes['created_by'] = $actorId;
        $attributes['updated_by'] = $actorId;
        $path = $image ? $this->images->store($image, 'places') : null;
        if ($path) {
            $attributes['image'] = $path;
        }
        try {
            $place = $this->repository->create($attributes);
        } catch (Throwable $exception) {
            $this->images->delete($path);
            throw $exception;
        }
        $this->logging->success('CreatePlace', $actorId);

        return $place;
    }

    public function update(array $data, Place $place, int $actorId, ?UploadedFile $image = null): Place
    {
        $this->ensurePresent($place);
        $attributes = Arr::only($data, self::FIELDS);
        if (array_key_exists('status', $attributes) && $attributes['status'] === null) {
            unset($attributes['status']);
        }
        $attributes['updated_by'] = $actorId;
        $oldPath = $place->image;
        $path = $image ? $this->images->store($image, 'places') : null;
        if ($path) {
            $attributes['image'] = $path;
        }
        try {
            $place = $this->repository->update($place, $attributes);
        } catch (Throwable $exception) {
            $this->images->delete($path);
            throw $exception;
        }
        if ($path) {
            $this->images->delete($oldPath);
        }
        $this->logging->success('UpdatePlace', $actorId);

        return $place;
    }

    public function delete(Place $place, int $actorId): Place
    {
        $this->ensurePresent($place);
        $place = $this->repository->delete($place, $actorId);
        $this->logging->success('DeletePlace', $actorId);

        return $place;
    }

    /** The caller owns the transaction covering the parent and its dependents. */
    public function deactivateByCategory(int $categoryId, int $actorId, bool $deleted = false): void
    {
        $attributes = ['status' => 0, 'updated_by' => $actorId];
        if ($deleted) {
            $attributes['deleted_at'] = now();
        }
        $this->repository->updateByCategory($categoryId, $attributes);
    }

    private function ensurePresent(Place $place): void
    {
        if ($place->deleted_at !== null) {
            throw (new ModelNotFoundException)->setModel(Place::class);
        }
    }
}
