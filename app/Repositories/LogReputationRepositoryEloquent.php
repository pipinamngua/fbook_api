<?php

namespace App\Repositories;

use App\Eloquent\LogReputation;
use App\Contracts\Repositories\LogReputationRepository;
use Illuminate\Pagination\Paginator;
use App\Exceptions\Api\ActionException;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknownException;
use Log;

class LogReputationRepositoryEloquent extends AbstractRepositoryEloquent implements LogReputationRepository
{
    public function model()
    {
        return new LogReputation;
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
}
