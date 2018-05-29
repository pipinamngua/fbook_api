<?php

namespace App\Eloquent;

class BookUser extends AbstractEloquent
{
    protected $table = 'book_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'status',
        'user_id',
        'book_id'
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
    
    public function logReputation()
    {
        return $this->morphOne(LogReputation::class, 'log_id');
    }
}
