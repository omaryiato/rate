<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Dashboard\UserService;
use App\Http\Resources\UserResource;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Dashboard\User\AddUser;
use App\Http\Requests\Dashboard\User\DeleteUser;
use App\Http\Requests\Dashboard\User\UpdateUser;
use App\Models\User;
use Exception;

class UserController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // Funtion to Get Users List
    public function index()
    {
        return ResponseHelper::success(
                UserResource::collection($this->userService->getAll()),
                [
                    'en' => trans('validation.data_retrieved', [], 'en'),
                    'ar' => trans('validation.data_retrieved', [], 'ar'),
                ],
                200);
    }

    //  Funtion to Get User Details
    public function show(User $user)
    {

        return ResponseHelper::success(
                new UserResource($this->userService->getDetails($user)),
                [
                    'en' => trans('validation.data_retrieved', [], 'en'),
                    'ar' => trans('validation.data_retrieved', [], 'ar'),
                ],
                200);

    }

    //  Funtion To Add New User
    public function store(AddUser $request)
    {
        try{


            return ResponseHelper::success(
                new UserResource($this->userService->create($request)),
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

    //  Funtion To Update User
    public function update(UpdateUser $request, User $user)
    {
        try {

            return ResponseHelper::success(
                new UserResource($this->userService->update($request, $user)),
                [
                    'en' => trans('validation.data_updated', [], 'en'),
                    'ar' => trans('validation.data_updated', [], 'ar'),
                ],
                201);

        } catch (Exception $exception) {
            return ResponseHelper::error(
                [
                    'en' => trans('validation.exception_error', [], 'en'),
                    'ar' => trans('validation.exception_error', [], 'ar'),
                ],
                $exception->getMessage(),
                500);
        }
    }

    // deleteUser Funtion To Delete User
    public function destroy(DeleteUser $request, User $user)
    {
        try {
            return ResponseHelper::success(
                    $this->userService->delete($request->validated(), $user),
                    [
                        'en' => trans('validation.data_deleted', [], 'en'),
                        'ar' => trans('validation.data_deleted', [], 'ar'),
                    ],
                    200);
        } catch (Exception $exception) {
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
