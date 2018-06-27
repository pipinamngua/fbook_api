<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Contracts\Repositories\PostRepository;
use App\Http\Requests\Api\Post\CreatePostRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\Api\ActionException;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknownException;
use Log;

class PostController extends ApiController
{
    protected $postRepository;

    public function __construct(PostRepository $repository)
    {
        parent::__construct($repository);
    }

    public function index()
    {
        return $this->getData(function () {
            $data = $this->repository->getData();
            $this->compacts['items'] = $this->reFormatPaginate($data);
        });
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        
        return $this->doAction(function () use ($data) {
            $this->compacts['items'] = $this->repository->store($data);
        });
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return $this->getData(function () use ($id) {
            $this->compacts['items'] = $this->repository->edit($id);
        });
    }

    public function updatePost(Request $request, $id)
    {
        $data = $request->all();

        try {
            return $this->doAction(function () use ($data, $id) {
                $post = $this->repository->findOrFail($id);

                $this->repository->update($post, $data);
            });
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }
    public function destroy($id)
    {
        try {
            return $this->doAction(function () use ($id) {
                $post = $this->repository->findOrFail($id);

                $this->repository->destroy($post);
            });
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }
}
