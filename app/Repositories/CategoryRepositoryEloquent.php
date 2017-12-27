<?php

namespace App\Repositories;

use App\Contracts\Repositories\CategoryRepository;
use App\Eloquent\Category;

class CategoryRepositoryEloquent extends AbstractRepositoryEloquent implements CategoryRepository
{
    public function model()
    {
        return new Category;
    }

    public function getData($data = [], $with = [], $dataSelect = ['*'])
    {
        $categories = $this->model()
            ->select($dataSelect)
            ->with($with)
            ->get();

        return $categories;
    }

    public function store(array $data)
    {
        return $this->model()->create($data);
    }

    public function update($categoryId, array $data)
    {
        try {
            $result = $this->model()
                ->where('id', $categoryId)
                ->update($data);
        } catch (Execption $e) {
            throw new QueryException($e->getMessage());
        }

        return $result;
    }
}
