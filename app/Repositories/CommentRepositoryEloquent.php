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
