<?php

namespace App\Repositories;

use App\Contracts\Repositories\OwnerRepository;
use App\Eloquent\Owner;

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
