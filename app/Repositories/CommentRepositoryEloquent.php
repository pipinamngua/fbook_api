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

    public function updateComment($dataComment)
    {
        try {
            $comment = Comment::findOrFail($dataComment['id']);
            $comment->content = $dataComment['content'];
            $comment->user_id = $dataComment['userId'];
            $comment->review_id = $dataComment['reviewId'];
            $comment->save();
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }
}
