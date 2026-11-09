<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    // use SoftDeletes;

    protected $table = 'category';

    protected $fillable = [
        'en_name',
        'ar_name',
        'description',
        'slug',
        'image',
        'icone',
        'status',
        'sort',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function places()
    {
        return $this->hasMany(Place::class, 'category_id', 'id');
    }
}
