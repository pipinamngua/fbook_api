<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\ReviewRepository;
use Illuminate\Http\Request;

class ReviewController extends ApiController
{
    public function __construct(ReviewRepository $repository)
    {
        parent::__construct($repository);
    }

    public function delete($reviewId)
    {
        return $this->doAction(function () use ($reviewId) {
            return $this->repository->delete($reviewId);
        }, __FUNCTION__);
    }

}
