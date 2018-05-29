<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Owner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        'added',
    ];

    protected $dates = ['deleted_at'];
}
