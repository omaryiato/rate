<?php

namespace App\Services\Client;

use App\Helpers\ContactMessageHelper;
use App\Models\Category;
use App\Models\Place;
use App\Repositories\Client\MainRepository;
use App\Services\LoggingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MainService
{
    public function __construct(
        protected MainRepository $repository,
        protected ContactMessageHelper $contactMessageHelper,
        protected LoggingService $loggingService
    ) {}

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function getPlaces(Category $category)
    {
        if ((int) $category->status !== 1) {
            throw (new ModelNotFoundException)->setModel(Category::class);
        }

        return $this->repository->getPlaces($category);
    }

    public function getReviews(Place $place)
    {
        if ((int) $place->status !== 1) {
            throw (new ModelNotFoundException)->setModel(Place::class);
        }

        return $this->repository->getReviews($place);
    }

    public function review(array $request, int $userId)
    {
        // Identity comes from the authenticated session, never from submitted user_id.
        $request['user_id'] = $userId;
        $review = $this->repository->review($this->prepareRequestInfo($request));
        $this->loggingService->success('CreateReview', $userId);

        return $review;
    }

    public function feedback(array $request)
    {
        $feedback = $this->repository->feedback($this->prepareFeedbackInfo($request));
        $this->loggingService->success('CreateFeedback');

        return $feedback;
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
