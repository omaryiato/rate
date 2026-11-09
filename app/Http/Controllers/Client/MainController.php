<?php

namespace App\Http\Controllers\Client;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Review;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\FeedbackResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ReviewResource;
use App\Models\Category;
use App\Services\Client\MainService;
use Exception;
use Illuminate\Http\Request;

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

    public function review(Review $request)
    {
        try{


            return ResponseHelper::success(
                new ReviewResource($this->service->review($request->validated())),
                [
                    'en' => trans('validation.review_product', [], 'en'),
                    'ar' => trans('validation.review_product', [], 'ar'),
                ],
                200
            );
        } catch(Exception $exception){
            return ResponseHelper::error(
                [
                    'en' => trans('validation.exception_error', [], 'en'),
                    'ar' => trans('validation.exception_error', [], 'ar'),
                ],
                $exception->getMessage(),
                500);
        }
    }
    public function feedback(Request $request)
    {
        try{
            return ResponseHelper::success(
                FeedbackResource::collection($this->service->feedback($request->all())),
                [
                    'en' => trans('validation.review_product', [], 'en'),
                    'ar' => trans('validation.review_product', [], 'ar'),
                ],
                200
            );
        } catch(Exception $exception){
            return ResponseHelper::error(
                [
                    'en' => trans('validation.exception_error', [], 'en'),
                    'ar' => trans('validation.exception_error', [], 'ar'),
                ],
                $exception->getMessage(),
                500);
        }
    }

}
