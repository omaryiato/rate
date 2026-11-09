<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaceBranche extends Model
{
    protected $table = "place_branches";

    protected $fillable = [
        "place_id",
        "en_name",
        "ar_name",
        'slug',
        'country_code',
        'city',
        "address",
        "latitude",
        "longitude",
        "phone",
        "image",
        "status",
        "is_main",
        "created_by",
        "updated_by",
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function palce(){
        return $this->belongsTo(Place::class, "place_id", 'id');
    }

    // public function reviews()
    // {
    //     return $this->hasMany(ProductReview::class, 'product_id', 'id');
    // }


}
