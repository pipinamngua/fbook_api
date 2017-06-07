<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\BookRepository;
use App\Http\Requests\Api\Book\BookFilterRequest;
use App\Http\Requests\Api\Book\SearchRequest;
use App\Exceptions\Api\ActionException;
use App\Http\Requests\Api\Book\IndexRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\Api\Book\BookingRequest;
use App\Eloquent\Book;
use App\Http\Requests\Api\Book\ReviewRequest;

class BookController extends ApiController
{
    public function __construct(BookRepository $repository)
    {
        parent::__construct($repository);
    }

    protected $select = [
        'id',
        'title',
        'description',
        'author',
        'publish_date',
        'total_page',
        'avg_star',
        'count_view',
        'status',
        'category_id',
        'office_id'
    ];

    protected $imageSelect = [
        'path',
        'size',
        'thumb_path',
        'target_id',
        'target_type',
    ];

    protected $categorySelect = [
        'id',
        'name',
    ];

    protected $officeSelect = [
        'id',
        'name',
    ];

    public function index(IndexRequest $request)
    {
        $field = $request->input('field');

        if (!$field) {
            throw new ActionException;
        }

        $relations = ['image'];

        return $this->getData(function () use ($relations, $field) {
            $data = $this->repository->getBooksByFields($relations, $this->select, $field);

            $this->compacts['item'] = $this->reFormatPaginate($data);
        });
    }

    protected function reFormatPaginate(LengthAwarePaginator $paginate)
    {
        $currentPage = $paginate->currentPage();

        return [
            'total' => $paginate->total(),
            'per_page' => $paginate->perPage(),
            'current_page' => $currentPage,
            'next_page' => ($paginate->lastPage() > $currentPage) ? $currentPage + 1 : null,
            'prev_page' => ($currentPage > 1) ? $currentPage - 1 : null,
            'data' => $paginate->items(),
        ];
    }

    public function show($id)
    {
        $this->compacts['item'] = $this->repository->show($id);

        return $this->jsonRender();
    }

    public function search(SearchRequest $request)
    {
        $data = $request->all();

        return $this->getData(function() use($data) {
            $this->compacts['items'] = $this->reFormatPaginate(
                $this->repository->getDataSearch($data, ['image', 'category', 'office'], $this->select)
            );
        });
    }

    public function booking(BookingRequest $request, $id)
    {
        $data = $request->all();

        return $this->doAction(function () use ($data, $id) {
            $book = $this->repository->findOrfail($id);

            $this->repository->booking($book, $data);
        });
    }

    public function loadConditionSort()
    {
        $this->compacts['items'] = array_values(config('model.filter_books'));

        return $this->jsonRender();
    }

    public function review(ReviewRequest $request, $bookId)
    {
        $data = $request->item;

        return $this->requestAction(function () use ($bookId, $data) {
            $this->repository->review($bookId, $data);
        });
    }

    public function filter(BookFilterRequest $request)
    {
        $field = $request->input('field');

        $input = $request->all();

        $relations = [
            'image' => function ($q) {
                $q->select($this->imageSelect);
            },
            'category' => function ($q) {
                $q->select($this->categorySelect);
            },
            'office' => function ($q) {
                $q->select($this->officeSelect);
            }
        ];

        return $this->getData(function () use ($relations, $field, $input) {
            $data = $this->repository->getBooksByFields($relations, $this->select, $field, $input);

            $this->compacts['item'] = $this->reFormatPaginate($data);
        });
    }
}