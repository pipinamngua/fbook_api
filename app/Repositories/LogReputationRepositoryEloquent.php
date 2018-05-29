<?php

namespace App\Repositories;

use App\Eloquent\LogReputation;
use App\Eloquent\UserFollow;
use App\Eloquent\Vote;
use App\Eloquent\Book;
use App\Eloquent\User;
use App\Contracts\Repositories\LogReputationRepository;
use Illuminate\Pagination\Paginator;
use App\Exceptions\Api\ActionException;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknownException;
use Illuminate\Support\Facades\DB;
use Log;

class LogReputationRepositoryEloquent extends AbstractRepositoryEloquent implements LogReputationRepository
{
    public function model()
    {
        return new LogReputation;
    }

    public function getData($data = [], $with = [], $dataSelect = ['*'])
    {
        $bookSelect = [
            'id',
            'title'
        ];
        $userSelect = [
            'id',
            'name',
            'avatar'
        ];
        $log = $this->model()
            ->select($dataSelect)
            ->with($with)
            ->get();
        foreach ($log as $logItem) {
            if ($logItem->log_type == config('model.log_type.share_book') || $logItem->log_type == config('model.log_type.add_owner')) {
                $logItem->pivot_data = json_decode($logItem->pivot_data, true);
                $logItem->book = $logItem->logBook()->select($bookSelect)->firstOrFail();
                $logItem->user = $logItem->userActionTo()->select($userSelect)->firstOrFail();
            } else if ($logItem->log_type == config('model.log_type.approve_borrow')) {
                $logItem->pivot_data = json_decode($logItem->pivot_data, true);
                $logItem->book_pivot = $logItem->logBook()->select($bookSelect)->firstOrFail();
                $logItem->user = $logItem->userActionTo()->select($userSelect)->firstOrFail();
                $logItem->owner = $logItem->ownerReceived()->select($userSelect)->firstOrFail();
            } else if ($logItem->log_type == config('model.log_type.be_upvoted')) {
                $vote = $logItem->vote()->firstOrFail();
                $logItem->review = $vote->reviewVote()->firstOrFail();
                $logItem->user_vote = $vote->userVote()->select($userSelect)->firstOrFail();
                $logItem->review_owner = $vote->reviewVote()->firstOrFail()->user()->select($userSelect)->firstOrFail();
            } else if ($logItem->log_type == config('model.log_type.be_followed')) {
                $userFollow = $logItem->userFollow()->firstOrFail();
                $logItem->follower = $userFollow->userFollower()->select($userSelect)->firstOrFail();
                $logItem->following = $userFollow->userFollowing()->select($userSelect)->firstOrFail();
            }
        }

        return $log;
    }

    public function addLog($logId, $logType, $point)
    {
        /* 
        Check if have array of pivot table or not
        */
        if (is_int($logId)) {
            $log = $this->model()->create([
                'log_id' => $logId,
                'log_type' => $logType,
                'point' => $point,
            ]);
        } else {
            $log = $this->model()->create([
                'log_type' => $logType,
                'pivot_data' => $logId,
                'point' => $point,
            ]);
        }

        return $log;
    }
    protected function getAction($with = [], $dataSelect = ['*'], $limit = '', $attribute = [], $officeId = '')
    {
        return $this->getBooksByBookUserStatus(
            config('model.book_user.status.returned'), $with, $dataSelect, $limit, $attribute, $officeId
        );
    }
}
