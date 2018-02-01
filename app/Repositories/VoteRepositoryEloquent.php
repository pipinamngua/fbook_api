<?php

namespace App\Repositories;

use App\Contracts\Repositories\VoteRepository;
use App\Eloquent\Vote;
use App\Eloquent\Review;

class VoteRepositoryEloquent extends AbstractRepositoryEloquent implements VoteRepository
{
    public function model()
    {
        return new Vote;
    }


    public function checkVoted($userId, $reviewId)
    {
        try {
            $check = $this->model()->where([
                ['user_id', '=', $userId],
                ['review_id', '=', $reviewId],
            ])->firstOrFail();

            return $check;
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }

    public function addNewVote($userId, $reviewId, $status)
    {
        try {
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
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
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
        try {
            $checkVoted = $this->model()->where([
                ['user_id', '=', $userId],
                ['review_id', '=', $reviewId],
            ])->firstOrFail();

            if ($checkVoted) {
                return $checkVoted->status;
            }

            return false;
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }
}
