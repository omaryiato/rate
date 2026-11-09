<?php

namespace App\Repositories\Dashboard;


use App\Models\Category;


class CategoryRepository {

    public function getAll()
    {
        return Category::with([
            'palces',
            // 'places.branches',
            // 'places.branches.reviews',
            'places.reviews',
        ])->get();
    }

    public function getDetails(Category $category){
        return $category->load([
            'palces',
            // 'places.branches',
            // 'places.branches.reviews',
            'places.reviews',
        ]);
    }

    public function create(array $request){
        return Category::create($request);
    }

    public function update(Category $category, array $request){
        $category->update($request);
        return $category;
    }

    public function deactivate(Category $category, array $request){
        // $category->places()->branches()->update([
        //     'status' => 0,
        //     'updated_by' => $request['updated_by'],
        //     'deleted_at' => now(),
        // ]);
        $category->places()->update([
            'status' => 0,
            'updated_by' => $request['updated_by'],
        ]);
        $category->update($request);
        return $category;
    }

    public function delete(array $request, Category $category){
        // $category->places()->branches()->update([
        //     'status' => 0,
        //     'updated_by' => $request['updated_by'],
        //     'deleted_at' => now(),
        // ]);
        $category->places()->update([
            'status' => 0,
            'updated_by' => $request['updated_by'],
            'deleted_at' => now(),
        ]);
        $category->update([
            'status' => 0,
            'updated_by' => $request['updated_by'],
            'deleted_at' => now(),
        ]);
        return $category;

        // $category->delete();
        // return $category;
    }

}
