<?php

namespace App\Repositories;

use App\Eloquent\Owner;
use App\Contracts\Repositories\OwnerRepository;
use App\Exceptions\Api\ActionException;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknownException;
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
}
