<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 
        'review_id', 
        'content'
    ];

    protected $appends = [
        'time_ago'
    ];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTimeAgoAttribute()
    {
        return \Carbon\Carbon::parse($this->created_at)->diffForHumans();
    }
}
