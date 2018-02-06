<?php

namespace App\Repositories;

use App\Eloquent\Comment;
use App\Contracts\Repositories\CommentRepository;
use App\Exceptions\Api\ActionException;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknownException;
use Log;

class CommentRepositoryEloquent extends AbstractRepositoryEloquent implements CommentRepository
{
    public function model()
    {
        return new Comment;
    }

    public function removeComment($commentId)
    {
        return $this->destroy($commentId);
    }
}
