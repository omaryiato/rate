<?php

namespace App\Services\Dashboard;

use App\Models\Place;
use App\Repositories\Dashboard\PlaceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;


class PlaceService
{

    protected $repository;
    public function __construct(PlaceRepository $repository,)
    {
        $this->repository = $repository;
    }

    // getAll Funtion To Get Product List
    public function getAll()
    {
        return  $this->repository->getAll();
    }

    // getDetails Funtion To Get Product Details
    public function getDetails(Place $place)
    {
        return $this->repository->getDetails($place);
    }

    // create Funtion To Add new Product
    public function create($request)
    {
        return DB::transaction(function () use ($request) {

            if ($request->hasFile('image')) {
                $image = $this->uploadImage(
                    $request->file('image'),
                    $request['en_name']
                );

                $request['image'] = $image;
            }

            return $this->repository->create($this->prepareRequestInfo($request));
        });
    }

    public function update($request, Place $place)
    {
        $request = $request->validated();
        return DB::transaction(function () use ($request, $place) {
            if ($request->hasFile('image')) {

                // Delete old image
                if ($place->image) {

                    $old_path = public_path($place->image);

                    if (File::exists($old_path)) {
                        File::delete($old_path);
                    }
                }


                // Upload new image
                $request['image'] = $this->uploadImage(
                    $request->file('image'),
                    $request['en_name']
                );

            } else {
                // keep old image
                $request['image'] = $place->image;
            }

            $request['slug'] = $request['slug'] ? $request['slug'] : $place->slug;
            $request['website_url'] = $request['website_url'] ? $request['website_url'] : $place->website_url;

            if($request['status'] == 0){
                return  $this->repository->deactivate(
                    $place,
                    $this->prepareRequestInfo($request)
                );


            } else {
                return $this->repository->update(
                    $place,
                    $this->prepareRequestInfo($request)
                );
            }
        });
    }


    public function prepareRequestInfo(array $product_request)
    {
        $request_data = [
            'category_id' => $request['category_id'] ?? null,
            'en_name' => $request['en_name'] ?? null,
            'ar_name' => $request['ar_name'] ?? null,
            'en_description' => $request['en_description'] ?? null,
            'ar_description' => $request['ar_description'] ?? null,
            'slug' => $request['slug'] ?? null,
            'website_url' => $request['website_url'] ?? null,
            'image' => $request['image'] ?? null,
            'status' => $request['status'] ?? 1,
        ];

        if (isset($product_request['created_by'])) {
            $request_data['created_by'] = $product_request['created_by'];
        }


        if (isset($product_request['updated_by'])) {
            $request_data['updated_by'] = $product_request['updated_by'];
        }


        return $request_data;
    }

    public function uploadImage(array $request, string $en_name)
    {

        $place_name = str_replace(' ', '_', strtolower($en_name));
        $extension = $request['image']->getClientOriginalExtension();
        $file_name = "{$place_name}_" . uniqid() . ".{$extension}";

        $path = public_path("documents/{$request['category_id']}/{$place_name}_product");

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $webp_name = pathinfo($file_name, PATHINFO_FILENAME) . '.webp';

        $image = Image::decode($request['image']);

        // encode to webp
        $encoded = $image->encodeUsingFormat(
            Format::WEBP,
            quality: 85
        );

        // save encoded image
        $encoded->save("{$path}/{$webp_name}");

        return "documents/{$request['category_id']}/{$place_name}_product/{$webp_name}";
    }


    // deleteProduct Funtion To Delete Product
    public function delete(array $request, Place $place)
    {
        return $this->repository->delete($request, $place);
    }


}

