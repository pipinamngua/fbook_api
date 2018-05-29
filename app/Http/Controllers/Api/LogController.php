<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\BookRepository;
use App\Contracts\Repositories\CategoryRepository;
use App\Contracts\Repositories\OfficeRepository;
use App\Contracts\Repositories\OwnerRepository;
use App\Contracts\Repositories\LogReputationRepository;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\ActionException;
use Illuminate\Http\Request;
use App\Events\NotificationHandler;
use App\Contracts\Repositories\UserRepository;
use App\Http\Requests\Api\Log\SearchLogRequest;
use App\Eloquent\User;
use App\Eloquent\Book;
use App\Eloquent\LogReputation;
use Log;

class LogController extends ApiController
{
    public function __construct(LogReputationRepository $repository)
    {
        parent::__construct($repository);
    }

    public function index()
    {
        return $this->getData(function() {
            $this->compacts['items'] = $this->repository->getData();
        });
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
