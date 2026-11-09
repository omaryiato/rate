<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Category\DeleteCategory;
use App\Services\Dashboard\CategoryService;
use App\Http\Resources\CategoryResource;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Dashboard\Category\AddCategory;
use App\Http\Requests\Dashboard\Category\UpdateCategory;
use App\Models\Category;
use Exception;

class CategoryController extends Controller
{

    protected $service;

    public function __construct(CategoryService $service) {
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
        try{
            return ResponseHelper::success(
                new CategoryResource($this->service->create($request)),
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
    public function update(UpdateCategory $request, Category $category)
    {
        try{

            return ResponseHelper::success(
                new CategoryResource(
                    $this->service->update(
                        $request, $category
                )),
                [
                    'en' => trans('validation.data_updated', [], 'en'),
                    'ar' => trans('validation.data_updated', [], 'ar'),
                ],
                201);

        } catch (Exception $exception){
            return ResponseHelper::error(
                [
                    'en' => trans('validation.exception_error', [], 'en'),
                    'ar' => trans('validation.exception_error', [], 'ar'),
                ],
                $exception->getMessage(),
                500);
        }
    }

    public function destroy(DeleteCategory $request, Category $category)
    {
        try{

            return  ResponseHelper::success(
                $this->service->delete($request->validated(), $category),
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
