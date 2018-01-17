<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'status',
    ];
}
