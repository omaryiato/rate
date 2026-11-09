<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Place\AddPlace;
use App\Http\Requests\Dashboard\Place\DeletePlace;
use App\Http\Requests\Dashboard\Place\UpdatePlace;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use App\Services\Dashboard\PlaceService;
use Exception;

class PlaceController extends Controller
{
    protected $service;
    public function __construct(PlaceService $service) {
        $this->service = $service;
    }

    public function index()
    {


        return ResponseHelper::success(
            PlaceResource::collection( $this->service->getAll()),
            [
                'en' => trans('validation.data_retrieved', [], 'en'),
                'ar' => trans('validation.data_retrieved', [], 'ar'),
            ],
            200);
    }

    public function show(Place $place)
    {

        return ResponseHelper::success(
            new PlaceResource($this->service->getDetails($place)),
            [
                'en' => trans('validation.data_retrieved', [], 'en'),
                'ar' => trans('validation.data_retrieved', [], 'ar'),
            ],
            200);
    }

    public function store(AddPlace $request)
    {
        try{

            return ResponseHelper::success(
                new PlaceResource($this->service->create($request)),
                [
                    'en' => trans('validation.data_added', [], 'en'),
                    'ar' => trans('validation.data_added', [], 'ar'),
                ],
                201);
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


    public function update(UpdatePlace $request, Place $place)
    {
        try{

            return ResponseHelper::success(
                new PlaceResource( $this->service->update($request, $place)),
                [
                    'en' => trans('validation.data_updated', [], 'en'),
                    'ar' => trans('validation.data_updated', [], 'ar'),
                ],
                201);
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

    public function destroy(DeletePlace $request, Place $place)
    {
        try{

            return ResponseHelper::success(
                $this->service->delete($request->validated(), $place),
                [
                    'en' => trans('validation.data_deleted', [], 'en'),
                    'ar' => trans('validation.data_deleted', [], 'ar'),
                ],
                200);
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
