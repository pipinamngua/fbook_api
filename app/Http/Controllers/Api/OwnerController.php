<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\OwnerRepository;

class OwnerController extends ApiController
{
    protected $ownerRepository;

    public function __construct(OwnerRepository $repository)
    {
        parent::__construct($repository);
    }

    public function countBookHaveOwner()
    {
        return $this->getData(function() {
            $this->compacts['item'] = $this->repository->countHaveOwner();
        });
    }

    public function countOwnerHaveBook()
    {
        return $this->getData(function() {
            $this->compacts['item'] = $this->repository->countOwner();
        });
    }
}
