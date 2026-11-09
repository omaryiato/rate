<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Place extends Model
{
    // use SoftDeletes;

    protected $table = 'places';

    protected $fillable = [
        'category_id',
        'en_name',
        'ar_name',
        'en_description',
        'ar_description',
        'slug',
        'website_url',
        'image',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function branches()
    {
        return $this->hasMany(PlaceBranche::class, 'place_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'place_id', 'id');
    }


}
