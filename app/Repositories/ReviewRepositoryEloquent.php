<?php

namespace App\Repositories;

use App\Contracts\Repositories\ReviewRepository;
use App\Eloquent\Review;

class ReviewRepositoryEloquent extends AbstractRepositoryEloquent implements ReviewRepository
{
    public function model()
    {
        return new Review;
    }

    public function delete($reviewId){
        return $this->model()->destroy($reviewId);
    }

    public function upVote($reviewId){

    }

    public function downVote($reviewId){

    }
}
