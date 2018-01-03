<?php

namespace App\Contracts\Repositories;

interface ReviewRepository extends AbstractRepository
{
    public function delete($reviewId);
    public function upVote($reviewId);
    public function downVote($reviewId);
}
