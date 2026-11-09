<?php

namespace App\Services\Dashboard;

use App\Models\Category;
use App\Repositories\Dashboard\CategoryRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;

class CategoryService
{

    protected $repository;
    public function __construct(CategoryRepository $repository) {
        $this->repository = $repository;
    }

    public function getAll(){
        return $this->repository->getAll();
    }

    public function getDetails(Category $category){
        return $this->repository->getDetails($category);
    }

    public function create($request)
    {
        $request = $request->validated();

        if ($request->hasFile('image')) {
            $image = $this->uploadImage(
                $request->file('image'),
                $request['en_name']
            );

            $request['image'] = $image;
        }

        return $this->repository->create(
            $this->prepareRequestInfo($request)
        );
    }

    public function update(object $request, Category $category)
    {
        $request = $request->validated();

        if ($request->hasFile('image')) {

            // Delete old image
            if ($category->image) {

                $old_path = public_path($category->image);

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
            $request['image'] = $category->image;
        }

        $request['slug'] = $request['slug'] ? $request['slug'] : $category->slug;
        $request['icone'] = $request['icone'] ? $request['icone'] : $category->icone;

        if($request['status'] == 0){
            return $this->repository->deactivate(
                $category,
                $this->prepareRequestInfo($request)
            );

        } else {
            return $this->repository->update(
                $category,
                $this->prepareRequestInfo($request)
            );
        }


    }

    public function delete(array $request, Category $category){
        return $this->repository->delete($request, $category);
    }

    public function uploadImage(UploadedFile $file, string $en_name)
    {
        $extension = $file->getClientOriginalExtension();

        $file_name = str_replace(' ', '_', $en_name) . '.' . $extension;

        $folder_path = public_path("documents/categories");


        if (!File::exists($folder_path)) {
            File::makeDirectory($folder_path, 0755, true);
        }

        $webp_name = pathinfo($file_name, PATHINFO_FILENAME) . '.webp';

        $image = Image::decode($file);

        // encode to webp
        $encoded = $image->encodeUsingFormat(
            Format::WEBP,
            quality: 85
        );

        // save encoded image
        $encoded->save("{$folder_path}/{$webp_name}");


        // $file->move($folder_path, $file_name);


        return "documents/categories/{$webp_name}";
    }

    public function prepareRequestInfo(array $request_info)
    {

        $request_data = [
            'en_name' => $request_info['en_name'] ?? null,
            'ar_name' => $request_info['ar_name'] ?? null,
            'description' => $request_info['description'] ?? null,
            'image' => $request_info['image'] ?? null,
            'status' => $request_info['status'] ?? 1,
            'slug' => $request_info['slug'] ?? null,
            'icone' => $request_info['icone'] ?? null,
            'sort' => $request_info['sort'] ?? 0,
        ];


        if (isset($request_info['created_by'])) {
            $request_data['created_by'] = $request_info['created_by'];
        }


        if (isset($request_info['updated_by'])) {
            $request_data['updated_by'] = $request_info['updated_by'];
        }


        return $request_data;
    }

}
