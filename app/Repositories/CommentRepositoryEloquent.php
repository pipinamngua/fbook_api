<?php

namespace App\Repositories;

use App\Contracts\Repositories\CommentRepository;
use App\Eloquent\Comment;

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
