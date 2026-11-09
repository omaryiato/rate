<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\User\AddUser;
use App\Http\Requests\Dashboard\User\DeleteUser;
use App\Http\Requests\Dashboard\User\UpdateUser;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Dashboard\UserService;

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
        return ResponseHelper::success(
            new UserResource($this->userService->create($request->validated(), (int) $request->user()->id, $request->file('avatar'))),
            [
                'en' => trans('validation.data_added', [], 'en'),
                'ar' => trans('validation.data_added', [], 'ar'),
            ],
            201);

    }

    //  Funtion To Update User
    public function update(UpdateUser $request, User $user)
    {
        return ResponseHelper::success(
            new UserResource($this->userService->update($request->validated(), $user, (int) $request->user()->id, $request->file('avatar'))),
            [
                'en' => trans('validation.data_updated', [], 'en'),
                'ar' => trans('validation.data_updated', [], 'ar'),
            ],
            200);
    }

    // deleteUser Funtion To Delete User
    public function destroy(DeleteUser $request, User $user)
    {
        return ResponseHelper::success(
            UserResource::make($this->userService->delete($user, (int) $request->user()->id)),
            [
                'en' => trans('validation.data_deleted', [], 'en'),
                'ar' => trans('validation.data_deleted', [], 'ar'),
            ],
            200);
    }
}
