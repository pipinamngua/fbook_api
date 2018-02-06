<?php

namespace App\Repositories;

use App\Eloquent\Office;
use App\Contracts\Repositories\OfficeRepository;
use App\Exceptions\Api\ActionException;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknownException;
use Log;

class OfficeRepositoryEloquent extends AbstractRepositoryEloquent implements OfficeRepository
{
    public function model()
    {
        return new Office;
    }

    public function getData($data = [], $with = [], $dataSelect = ['*'])
    {
        $categories = $this->model()
            ->select($dataSelect)
            ->with($with)
            ->get();

        return $categories;
    }
}
