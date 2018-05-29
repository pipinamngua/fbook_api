<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'review_id',
        'status',
    ];

    public function userVote()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewVote()
    {
        return $this->belongsTo(Review::class, 'review_id');
    }
    
    public function logReputation()
    {
        return $this->morphOne(LogReputation::class, 'log_id');
    }
}
