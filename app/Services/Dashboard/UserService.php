<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Repositories\Dashboard\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;

class UserService
{

    protected $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    // getAll Funtion To Get Users List
    public function getAll()
    {
        return  $this->repository->getAll();
    }

    // getDetails Funtion To Get User Details
    public function getDetails(User  $user)
    {

        return  $this->repository->getDetails($user);
    }

    // create Funtion To Add new User
    public function create( $request)
    {
        if ($request->hasFile('avatar')) {
            $avatar = $this->uploadImage(
                $request->file('avatar'),
                $request['user_name']
            );

            $request['avatar'] = $avatar;
        }

        return $this->repository->create($this->prepareRequestInfo($request));
    }

    // update Funtion To Update User info
    public function update( $request, User $user)
    {
        if ($request->hasFile('avatar')) {

            // Delete old avatar
            if ($user->avatar) {

                $old_path = public_path($user->avatar);

                if (File::exists($old_path)) {
                    File::delete($old_path);
                }
            }

            // Upload new avatar
            $request['avatar'] = $this->uploadImage(
                $request->file('avatar'),
                $request['user_name']
            );

        } else {
            // keep old avatar
            $request['avatar'] = $user->avatar;
        }

        return $this->repository->update($user, $this->prepareRequestInfo($request));
    }

    // delete Funtion To Delete User
    public function delete(array $request, User $user)
    {
        return $this->repository->delete($request, $user);
    }

    public function prepareRequestInfo(array $request_info)
    {

        $request_data = [
            'full_name' => $request_info['full_name'] ?? null,
            'user_name' => $request_info['user_name'] ?? null,
            'phone_number' => $request_info['phone_number'] ?? null,
            'email_address' => $request_info['email_address'] ?? null,
            'password' => isset($request_info['password'])
                    ? Hash::make($request_info['password'])
                    : null,
            'status' => $request_info['status'] ?? null,
            'type' => $request_info['type'] ?? null,
            'avatar' => $request_info['avatar'] ?? null,
        ];

        return $request_data;
    }

    public function uploadImage(array $request, string $user_name)
    {

        $user_name = str_replace(' ', '_', strtolower($user_name));
        $extension = $request['avatar']->getClientOriginalExtension();
        $file_name = "{$user_name}_" . uniqid() . ".{$extension}";

        $path = public_path("documents/users/{$user_name}");

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $webp_name = pathinfo($file_name, PATHINFO_FILENAME) . '.webp';

        $avatar = Image::decode($request['avatar']);

        // encode to webp
        $encoded = $avatar->encodeUsingFormat(
            Format::WEBP,
            quality: 85
        );

        // save encoded image
        $encoded->save("{$path}/{$webp_name}");

        return "documents/users/{$user_name}/{$webp_name}";
    }

}

