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
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    protected $service;

    public function __construct(PlaceService $service)
    {
        $this->service = $service;
    }

    public function index()
    {

        return ResponseHelper::success(
            PlaceResource::collection($this->service->getAll()),
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
        return ResponseHelper::success(
            new PlaceResource($this->service->create($request->validated(), (int) $request->user()->id, $request->file('image'))),
            [
                'en' => trans('validation.data_added', [], 'en'),
                'ar' => trans('validation.data_added', [], 'ar'),
            ],
            201);
    }

    public function update(UpdatePlace $request, Place $place)
    {
        return ResponseHelper::success(
            new PlaceResource($this->service->update($request->validated(), $place, (int) $request->user()->id, $request->file('image'))),
            [
                'en' => trans('validation.data_updated', [], 'en'),
                'ar' => trans('validation.data_updated', [], 'ar'),
            ],
            200);
    }

    public function destroy(Request $request, Place $place)
    {
        return ResponseHelper::success(
            new PlaceResource($this->service->delete($place, (int) $request->user()->id)),
            [
                'en' => trans('validation.data_deleted', [], 'en'),
                'ar' => trans('validation.data_deleted', [], 'ar'),
            ],
            200);
    }
}
