<?php

namespace App\Contracts\Repositories;

interface OwnerRepository extends AbstractRepository
{
    public function countHaveOwner();

    public function countOwner();
}
