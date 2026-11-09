<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    // use SoftDeletes;

    protected $table = 'feedback';

    public $timestamps = false;

    protected $fillable = [
        'subject',
        'description',
        'created_at',
    ];

    protected $casts = [
        'description' => 'array',
        'created_at' => 'datetime',
    ];

}
