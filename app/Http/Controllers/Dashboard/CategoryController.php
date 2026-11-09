<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Category\AddCategory;
use App\Http\Requests\Dashboard\Category\DeleteCategory;
use App\Http\Requests\Dashboard\Category\UpdateCategory;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\Dashboard\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    public function index()
    {

        return ResponseHelper::success(
            CategoryResource::collection($this->service->getAll()),
            [
                'en' => trans('validation.data_retrieved', [], 'en'),
                'ar' => trans('validation.data_retrieved', [], 'ar'),
            ],
            200);
    }

    public function show(Category $category)
    {

        return ResponseHelper::success(
            new CategoryResource($this->service->getDetails($category)),
            [
                'en' => trans('validation.data_retrieved', [], 'en'),
                'ar' => trans('validation.data_retrieved', [], 'ar'),
            ],
            200);
    }

    public function store(AddCategory $request)
    {
        return ResponseHelper::success(
            new CategoryResource($this->service->create($request->validated(), (int) $request->user()->id, $request->file('image'))),
            [
                'en' => trans('validation.data_added', [], 'en'),
                'ar' => trans('validation.data_added', [], 'ar'),
            ],
            201);
    }

    public function update(UpdateCategory $request, Category $category)
    {
        return ResponseHelper::success(
            new CategoryResource(
                $this->service->update(
                    $request->validated(), $category, (int) $request->user()->id, $request->file('image')
                )),
            [
                'en' => trans('validation.data_updated', [], 'en'),
                'ar' => trans('validation.data_updated', [], 'ar'),
            ],
            200);
    }

    public function destroy(Request $request, Category $category)
    {
        return ResponseHelper::success(
            new CategoryResource($this->service->delete($category, (int) $request->user()->id)),
            [
                'en' => trans('validation.data_deleted', [], 'en'),
                'ar' => trans('validation.data_deleted', [], 'ar'),
            ],
            200);

    }
}
