<?php

namespace App\Contracts\Repositories;

use Illuminate\Http\Request;
use App\Eloquent\Post;

interface PostRepository extends AbstractRepository
{
    public function getDataPostHome($dataSelect = ['*']);

    public function getData($data = [], $with = [], $dataSelect = ['*']);

    public function getDataPost($id);

    public function store(array $data);

    public function edit($id);

    public function update(Post $post, array $data);

    public function destroy(Post $post);
}
