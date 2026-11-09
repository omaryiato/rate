<?php

namespace App\Repositories\Dashboard;


use App\Models\Place;


class PlaceRepository
{

    // getAll Funtion To Get Product List
    public function getAll()
    {
        return Place::with([
            'reviews',
            'category'
            ])->get();
    }

    // getDetails Funtion To Get Product Details
    public function getDetails(Place $place)
    {
        return $place->load([
            'reviews',
            'category'
        ]);
    }

    public function create(array $request)
    {
        return Place::create($request);
    }


    public function update(Place $place, array $request){
        $place->update($request);
        return $place;
    }

    public function deactivate(Place $place, array $request){
        // $place->branches()->update([
        //     'status' => 0,
        //     'updated_by' => $request['updated_by'],
        //     'deleted_at' => now(),
        // ]);
        $place->update($request);
        return $place;
    }

    // delete Funtion To Delete Category
    public function delete(array $request, Place $place)
    {
        // $place->branches()->update([
        //     'status' => 0,
        //     'updated_by' => $request['updated_by'],
        //     'deleted_at' => now(),
        // ]);

        $place->update([
            'status' => 0,
            'updated_by' => $request['updated_by'],
            'deleted_at' => now(),
        ]);
        // $place->delete();
        // return $place;
    }
}

