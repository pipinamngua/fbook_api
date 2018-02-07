<?php

namespace App\Contracts\Repositories;

interface CommentRepository extends AbstractRepository
{
    public function removeComment($id);

    public function updateComment($dataComment);
}
