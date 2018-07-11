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
use Carbon\Carbon;
use Illuminate\Support\collection;

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
            } elseif ($logItem->log_type == config('model.log_type.approve_borrow')) {
                $logItem->pivot_data = json_decode($logItem->pivot_data, true);
                $logItem->book_pivot = $logItem->logBook()->select($bookSelect)->firstOrFail();
                $logItem->user = $logItem->userActionTo()->select($userSelect)->firstOrFail();
                $logItem->owner = $logItem->ownerReceived()->select($userSelect)->firstOrFail();
            } elseif ($logItem->log_type == config('model.log_type.be_upvoted')) {
                $vote = $logItem->vote()->firstOrFail();
                $logItem->review = $vote->reviewVote()->firstOrFail();
                $logItem->user_vote = $vote->userVote()->select($userSelect)->firstOrFail();
                $logItem->review_owner = $vote->reviewVote()->firstOrFail()->user()->select($userSelect)->firstOrFail();
            } elseif ($logItem->log_type == config('model.log_type.be_followed')) {
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
            config('model.book_user.status.returned'),
            $with,
            $dataSelect,
            $limit,
            $attribute,
            $officeId
        );
    }

    public function topHotUser()
    {
        $userSelect = [
            'id',
            'name',
            'avatar',
            'office_id'
        ];
        $temp = collect([]);
        $date = date_modify(Carbon::now(), '-7 days');
        $log = $this->model()
            ->where('created_at', '>=', $date)
            ->get();
        foreach ($log as $logItem) {
            if ($logItem->log_type == config('model.log_type.share_book') || $logItem->log_type == config('model.log_type.add_owner')) {
                $logItem->pivot_data = json_decode($logItem->pivot_data, true);
                $logItem->user= $logItem->userActionTo()->select($userSelect)->firstOrFail();
            } elseif ($logItem->log_type == config('model.log_type.approve_borrow')) {
                $logItem->pivot_data = json_decode($logItem->pivot_data, true);
                $logItem->user = $logItem->ownerReceived()->select($userSelect)->firstOrFail();
            } elseif ($logItem->log_type == config('model.log_type.be_upvoted')) {
                $vote = $logItem->vote()->firstOrFail();
                $logItem->user = $vote->reviewVote()->firstOrFail()->user()->select($userSelect)->firstOrFail();
            } elseif ($logItem->log_type == config('model.log_type.be_followed')) {
                $userFollow = $logItem->userFollow()->firstOrFail();
                $logItem->user = $userFollow->userFollowing()->select($userSelect)->firstOrFail();
            }
        }
        $log = $log->groupBy('user.id')->toArray();
        list($key, $value) = array_divide($log);
        for ($i=0; $i < count($key); $i++) {
            $sum = 0;
            foreach ($log[$key[$i]] as $item) {
                $sum += $item['point'];
            }
            $temp->push(['user' => $log[$key[$i]][0]['user'], 'sum' => $sum]);
        }
        $temp = $temp->sortByDesc('sum');

        return $temp->take(config('model.top_hot_user.top'));
    }

    public function getDataSearchReciverPoint($name)
    {
        $logs = $this->getData();
        $results = collect([]);

        foreach ($logs as $log) {
            if ($log->log_type == config('model.log_type.share_book')) {
                $flag = str_contains(strtolower($log->user->name), strtolower($name));
                if ($flag) {
                    $results->push($log);
                }
            } elseif ($log->log_type == config('model.log_type.add_owner')) {
                $flag = str_contains(strtolower($log->user->name), strtolower($name));
                if ($flag) {
                    $results->push($log);
                }
            } elseif ($log->log_type == config('model.log_type.approve_borrow')) {
                $flag = str_contains(strtolower($log->owner->name), strtolower($name));
                if ($flag) {
                    $results->push($log);
                }
            } elseif ($log->log_type == config('model.log_type.be_upvoted')) {
                $flag = str_contains(strtolower($log->review_owner->name), strtolower($name));
                if ($flag) {
                    $results->push($log);
                }
            } elseif ($log->log_type == config('model.log_type.be_followed')) {
                $flag = str_contains(strtolower($log->following->name), strtolower($name));
                if ($flag) {
                    $results->push($log);
                }
            }
        }
        
        return $results;
    }
}
