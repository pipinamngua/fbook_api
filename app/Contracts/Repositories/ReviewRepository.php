<?php

namespace App\Contracts\Repositories;

interface ReviewRepository extends AbstractRepository
{
    public function delete($reviewId);
    public function vote($userId, $reviewId, $status);
    public function reviewDetails($reviewId, $userId);
    public function increaseVote($reviewId, $voteNumber);
    public function decreaseVote($reviewId, $voteNumber);
}
