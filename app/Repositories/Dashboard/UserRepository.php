<?php

namespace App\Repositories\Dashboard;

use App\Models\User;



class UserRepository
{

    // getAll Funtion To Get Users List
    public function getAll()
    {
        return User::with([
            'reviews',
        ])->get();
    }

    // getDetails Funtion To Get User Details
    public function getDetails(User $user)
    {
        return $user->load([
            'reviews',
        ]);
    }

    // create Funtion To Add new User
    public function create(array $request)
    {
        return User::create($request);
    }

    // update Funtion To Update User info
    public function update(User $user, array $request)
    {
        $user->update($request);
        return $user;
    }

    public function deactivate(User $user, array $request){
        // $user->branches()->update([
        //     'status' => 0,
        //     'updated_by' => $request['updated_by'],
        //     'deleted_at' => now(),
        // ]);
        $user->update($request);
        return $user;
    }


    // delete Funtion To Delete User
    public function delete(array $request, User $user)
    {

        $user->update([
            'status' => 0,
            // 'updated_by' => $request['updated_by'],
            'deleted_at' => now(),
        ]);
        return $user;
        // $user->delete();
        // return $user;
    }
}

