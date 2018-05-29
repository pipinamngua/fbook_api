<?php

namespace App\Eloquent;

class LogReputation extends AbstractEloquent
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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function log()
    {
        return $this->morphTo();
    }
}
