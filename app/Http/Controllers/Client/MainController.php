<?php

namespace App\Http\Controllers\Client;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Review;
use App\Http\Requests\Client\StoreFeedbackRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\FeedbackResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ReviewResource;
use App\Models\Category;
use App\Models\Place;
use App\Services\Client\MainService;

class MainController extends Controller
{
    public function __construct(
        protected MainService $service
    ) {}

    public function index()
    {

        return ResponseHelper::success(
            CategoryResource::collection($this->service->getAll()),
            [
                'en' => trans('validation.home_page', [], 'en'),
                'ar' => trans('validation.home_page', [], 'ar'),
            ],
            200
        );
    }

    public function places(Category $category)
    {

        return ResponseHelper::success(
            PlaceResource::collection($this->service->getPlaces($category)),
            [
                'en' => trans('validation.home_page', [], 'en'),
                'ar' => trans('validation.home_page', [], 'ar'),
            ],
            200
        );
    }

    public function reviews(Place $place)
    {
        return ResponseHelper::success(
            ReviewResource::collection($this->service->getReviews($place)),
            ['ar' => 'تم جلب التقييمات بنجاح', 'en' => 'Reviews retrieved successfully'],
        );
    }

    public function review(Review $request)
    {
        return ResponseHelper::success(
            new ReviewResource($this->service->review($request->validated(), (int) $request->user()->id)),
            [
                'en' => trans('validation.review_product', [], 'en'),
                'ar' => trans('validation.review_product', [], 'ar'),
            ],
            201
        );
    }

    public function feedback(StoreFeedbackRequest $request)
    {
        return ResponseHelper::success(
            FeedbackResource::make($this->service->feedback($request->validated())),
            [
                'en' => 'Feedback received successfully',
                'ar' => 'تم استلام الملاحظة بنجاح',
            ],
            201
        );
    }
}
