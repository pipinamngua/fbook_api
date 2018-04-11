<?php

namespace App\Repositories;

use App\Eloquent\Owner;
use App\Contracts\Repositories\OwnerRepository;
use App\Exceptions\Api\ActionException;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknownException;
use Illuminate\Support\Facades\DB;
use Log;

class OwnerRepositoryEloquent extends AbstractRepositoryEloquent implements OwnerRepository
{
    public function model()
    {
        return new Owner;
    }

    public function countHaveOwner()
    {
        return $this->model()
            ->distinct('book_id')
            ->count('book_id');
    }

    public function countOwner()
    {
        return $this->model()
            ->distinct('user_id')
            ->count('user_id');
    }
    public function topOwnBook()
    {
        return $this->model()->select(DB::raw('count(owners.user_id) as user_count, users.*, owners.user_id'))
            ->leftJoin('users', 'owners.user_id', '=', 'users.id')
            ->groupBy('owners.user_id')
            ->orderBy('user_count', 'desc')
            ->take(config('model.top_owner.top'))
            ->get();
    }
}
