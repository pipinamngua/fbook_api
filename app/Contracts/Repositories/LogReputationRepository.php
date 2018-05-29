<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Eloquent\Media;

interface LogReputationRepository extends AbstractRepository
{
    public function addLog($logId, $logType, $point);
}
