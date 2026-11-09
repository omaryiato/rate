<?php

namespace App\Services\Client;

use App\Helpers\ContactMessageHelper;
use App\Models\Category;
use App\Repositories\Client\MainRepository;

class MainService
{
    public function __construct(
        protected MainRepository $repository,
        protected ContactMessageHelper $contactMessageHelper
    ) {}

    public function getAll()
    {
        return $this->repository->getAll();
    }
    public function getPlaces(Category $category)
    {
        return $this->repository->getPlaces($category);
    }

    public function review(array $request)
    {
        return $this->repository->review($this->prepareRequestInfo($request));
    }

    public function feedback(array $request)
    {
        return $this->repository->feedback($this->prepareFeedbackInfo($request));
    }

    public function prepareRequestInfo(array $request_info)
    {
        $request_data = [
            'user_id' => $request_info['user_id'] ?? null,
            'place_id' => $request_info['place_id'] ?? null,
            'comment' => $request_info['comment'] ?? null,
            'rating' => $request_info['rating'] ?? 1,
        ];

        return $request_data;
    }

    public function prepareFeedbackInfo(array $request_info)
    {
        $request_data = [
            'subject' => $request_info['subject'] ?? null,
            'description' => $request_info['description'] ?? null,
            'created_at' => now(),
        ];

        return $request_data;
    }


}
