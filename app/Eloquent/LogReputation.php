<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;

class LogReputation extends Model
{
    protected $table = 'log_reputations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'log_id',
        'log_type',
        'point',
        'pivot_data',
        'created_at',
    ];
    protected $hidden = [
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function log()
    {
        return $this->morphTo();
    }

    public function userFollow()
    {
        return $this->belongsTo(UserFollow::class, 'log_id');
    }

    public function getPivotUserIdAttribute()
    {
        return $this->pivot_data['user_id'] ?? null;
    }

    public function getPivotBookIdAttribute()
    {
        return $this->pivot_data['book_id'] ?? null;
    }

    public function getPivotOwnerIdAttribute()
    {
        return $this->pivot_data['owners_id'] ?? null;
    }

    //get book data of action in pivot_data
    public function logBook()
    {
        return $this->belongsTo(Book::class, 'pivot_book_id');
    }

    //get user data of action in pivot_data
    public function userActionTo()
    {

        return $this->belongsTo(User::class, 'pivot_user_id');
    }

    //get owner data of book in pivot_data
    public function ownerReceived()
    {
        return $this->belongsTo(User::class, 'pivot_owner_id');
    }

    //get vote data of action vote up
    public function vote()
    {
        return $this->belongsTo(Vote::class, 'log_id');
    }
}
