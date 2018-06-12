<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserFollow extends Model
{
    use SoftDeletes;
    
    protected $table = 'user_follow';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'following_id',
        'follower_id',
    ];

    public function userFollower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function userFollowing()
    {
        return $this->belongsTo(User::class, 'following_id');
    }

    public function logReputation()
    {
        return $this->morphOne(LogReputation::class, 'log_id');
    }
}
