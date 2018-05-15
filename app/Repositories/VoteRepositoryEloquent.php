<?php

namespace App\Repositories;

use App\Eloquent\Vote;
use App\Eloquent\Review;
use App\Contracts\Repositories\VoteRepository;
use App\Exceptions\Api\ActionException;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknownException;
use Log;

class VoteRepositoryEloquent extends AbstractRepositoryEloquent implements VoteRepository
{
    public function model()
    {
        return new Vote;
    }


    public function checkVoted($userId, $reviewId)
    {
        $check = $this->model()->where([
            ['user_id', '=', $userId],
            ['review_id', '=', $reviewId],
        ])->first();

        return $check;
    }

    public function addNewVote($userId, $reviewId, $status)
    {
        $review = Review::findOrFail($reviewId);
        if ($review->user_id == $userId) {
            return false;
        }
        $this->model()->insert([
            [
                'user_id' => $userId,
                'review_id' => $reviewId,
                'status' => $status
            ],
        ]);

        return true;
    }

    public function changeStatus($userId, $reviewId, $status)
    {
        $this->model()->where([
            ['user_id', '=', $userId],
            ['review_id', '=', $reviewId],
        ])->update(['status' => $status]);
    }

    public function checkUserVoted($userId, $reviewId)
    {
        $checkVoted = $this->model()->where([
            ['user_id', '=', $userId],
            ['review_id', '=', $reviewId],
        ])->first();

        if ($checkVoted) {
            return $checkVoted->status;
        }

        return false;
    }

    public function checkUpVoted($userId, $reviewId)
    {
        return $this->model()->where([
            ['user_id', '=', $userId],
            ['review_id', '=', $reviewId],
            ['status', '=', config('model.request_vote.up_vote')],
        ])->first();
    }
}
