<?php

namespace App\Repositories;

use App\Contracts\Repositories\BookRepository;
use App\Eloquent\Book;
use App\Eloquent\BookUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknownException;
use Log;

class BookRepositoryEloquent extends AbstractRepositoryEloquent implements BookRepository
{
    public function model()
    {
        return new \App\Eloquent\Book;
    }

    public function getDataInHomepage($with = [], $dataSelect = ['*'])
    {
        $limit = config('paginate.book_home_limit');

        return [
            [
                'key' => config('model.filter_books.latest.key'),
                'title' => config('model.filter_books.latest.title'),
                'data' => $this->getLatestBooks($with, $dataSelect, $limit)->items(),
            ],
            [
                'key' => config('model.filter_books.view.key'),
                'title' => config('model.filter_books.view.title'),
                'data' => $this->getBooksByCountView($with, $dataSelect, $limit)->items(),
            ],
            [
                'key' => config('model.filter_books.rating.key'),
                'title' => config('model.filter_books.rating.title'),
                'data' => $this->getBooksByRating($with, $dataSelect, $limit)->items(),
            ],
            [
                'key' => config('model.filter_books.waiting.key'),
                'title' => config('model.filter_books.waiting.title'),
                'data' => $this->getBooksByWaiting($with, $dataSelect, $limit)->items(),
            ],
        ];
    }

    public function getDataFilterInHomepage($with = [], $dataSelect = ['*'], $attribute = [])
    {
        $limit = config('paginate.book_home_limit');

        return [
            [
                'key' => config('model.filter_books.latest.key'),
                'title' => config('model.filter_books.latest.title'),
                'data' => $this->getLatestBooks($with, $dataSelect, $limit, $attribute)->items(),
            ],
            [
                'key' => config('model.filter_books.view.key'),
                'title' => config('model.filter_books.view.title'),
                'data' => $this->getBooksByCountView($with, $dataSelect, $limit, $attribute)->items(),
            ],
            [
                'key' => config('model.filter_books.rating.key'),
                'title' => config('model.filter_books.rating.title'),
                'data' => $this->getBooksByRating($with, $dataSelect, $limit, $attribute)->items(),
            ],
            [
                'key' => config('model.filter_books.waiting.key'),
                'title' => config('model.filter_books.waiting.title'),
                'data' => $this->getBooksByWaiting($with, $dataSelect, $limit, $attribute)->items(),
            ],
        ];
    }

    public function getDataSearch(array $attribute, $with = [], $dataSelect = ['*'])
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
            ->select($dataSelect)
            ->with($with)
            ->where(function ($query) use ($attribute) {
                if (isset($attribute['conditions']) && $attribute['conditions']) {
                    foreach ($attribute['conditions'] as $conditions) {
                        foreach ($conditions as $type => $typeIds) {
                            if (in_array($type, config('model.filter_type')) && count($typeIds)) {
                                $query->whereIn($type . '_id', $typeIds);
                            }
                        }
                    }
                }
                if (isset($attribute['search']['keyword']) && $attribute['search']['keyword']) {
                    $query->where(function ($query) use($attribute) {
                        if (isset($attribute['search']['field']) && $attribute['search']['field']) {
                            $query->where($attribute['search']['field'], 'LIKE', '%' . $attribute['search']['keyword'] . '%');
                        } else {
                            foreach (config('model.book.fields') as $field) {
                                $query->where($field, 'LIKE', '%' . $attribute['search']['keyword'] . '%');
                            }
                        }
                    });
                }
            })
            ->orderBy($input['sort']['field'], $input['sort']['type'])
            ->paginate(config('paginate.default'));
    }

    protected function getLatestBooks($with = [], $dataSelect = ['*'], $limit = '', $attribute = [])
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
            ->select($dataSelect)
            ->with($with)
            ->getData(config('model.filter_books.latest.field'), $input['filters'])
            ->orderBy($input['sort']['field'], $input['sort']['type'])
            ->paginate($limit ?: config('paginate.default'));
    }

    protected function getBooksByCountView($with = [], $dataSelect = ['*'], $limit = '', $attribute = [])
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
            ->select($dataSelect)
            ->with($with)
            ->getData(config('model.filter_books.view.field'), $input['filters'])
            ->orderBy($input['sort']['field'], $input['sort']['type'])
            ->paginate($limit ?: config('paginate.default'));
    }

    protected function getBooksByRating($with = [], $dataSelect = ['*'], $limit = '', $attribute = [])
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
            ->select($dataSelect)
            ->with($with)
            ->getData(config('model.filter_books.view.field'), $input['filters'])
            ->orderBy($input['sort']['field'], $input['sort']['type'])
            ->paginate($limit ?: config('paginate.default'));
    }

    protected function getBooksByWaiting($with = [], $dataSelect = ['*'], $limit = '', $attribute = [])
    {
        $input = $this->getDataInput($attribute);

        $numberOfUserWaitingBook = \DB::table('books')
            ->join('book_user', 'books.id', '=', 'book_user.book_id')
            ->select('book_user.book_id', \DB::raw('count(book_user.user_id) as count_waiting'))
            ->where('book_user.status', Book::STATUS['waiting'])
            ->groupBy('book_user.book_id')
            ->orderBy('count_waiting', 'DESC')
            ->limit($limit ?: config('paginate.default'))
            ->get();

        $books = $this->model()
            ->select($dataSelect)
            ->with($with)
            ->whereIn('id', $numberOfUserWaitingBook->pluck('book_id')->toArray())
            ->getData($input['sort']['field'], $input['filters'], $input['sort']['type'])
            ->paginate($limit ?: config('paginate.default'));

        foreach ($books->items() as $book) {
            $book->count_waiting = $numberOfUserWaitingBook->where('book_id', $book->id)->first()->count_waiting;
        }

        return $books;
    }

    public function getBooksByFields($with = [], $dataSelect = ['*'], $field, $attribute = [])
    {
        switch ($field) {
            case config('model.filter_books.view.key'):
                return $this->getBooksByCountView($with, $dataSelect,'', $attribute);

            case config('model.filter_books.latest.key'):
                return $this->getLatestBooks($with, $dataSelect, '', $attribute);

            case config('model.filter_books.rating.key'):
                return $this->getBooksByRating($with, $dataSelect, '', $attribute);

            case config('model.filter_books.waiting.key'):
                return $this->getBooksByWaiting($with, $dataSelect, '', $attribute);
        }
    }

    public function booking(Book $book, array $attributes)
    {
        $bookUpdate = array_only($attributes['item'], app(BookUser::class)->getFillable());
        $checkUser = $book->users()->find($this->user->id);

        if ($checkUser && $checkUser->pivot->status == config('model.book_user.status.reading')) {
            $book->update(['status' => config('model.book.status.available')]);
            $book->userReadingBook()->updateExistingPivot($this->user->id, ['status' => config('model.book_user.status.done')]);
        } else {
            $userWaiting = $book->users()
                ->where('user_id', '<>', $this->user->id)
                ->count();

            if ($userWaiting) {
                if (!$checkUser) {
                    $book->users()->attach($this->user->id, [
                        'user_id' => $this->user->id,
                        'book_id' => $bookUpdate['book_id'],
                        'status' => config('model.book_user.status.waiting')
                    ]);
                } else {
                    $book->users()->updateExistingPivot($this->user->id, ['status' => config('model.book_user.status.waiting')]);
                }
            } else {
                $book->users()->updateExistingPivot($this->user->id, ['book_user.status' => config('model.book_user.status.reading')]);
                $book->where('id', $bookUpdate['book_id'])->update(['status' => config('model.book.status.unavailable')]);
            }
        }
    }

    public function review($bookId, array $data)
    {
        $book = $this->model()->findOrFail($bookId);
        $dataReview = array_only($data, ['content', 'star']);
        $dataReview['created_at'] = $dataReview['updated_at'] = Carbon::now();

        $book->reviews()->attach([
            $this->user->id => $dataReview
        ]);

        if (isset($dataReview['star'])) {
            Event::fire('books.averageStar', [
                [
                    'book' => $book,
                    'star' => $dataReview['star'],
                ]
            ]);
        }
    }

    protected function getDataInput($attribute = [])
    {
        $sort = [
            'field' => 'created_at',
            'type' => 'desc'
        ];
        $filters = [];

        if (isset($attribute['sort']['field']) && $attribute['sort']['field']) {
            $sort['field'] = $attribute['sort']['field'];
        }

        if (isset($attribute['sort']['order_by']) && $attribute['sort']['order_by']) {
            $sort['type'] = $attribute['sort']['order_by'];
        }

        if (isset($attribute['filters']) && $attribute['filters']) {
            $filters = $attribute['filters'];
        }

        return compact('sort', 'filters');
    }

    public function show($id)
    {
        try {
            $book = $this->model()->findOrFail($id);
            
            return $book->load(['image', 'reviewsDetailBook',
                'userReadingBook' => function ($query) {
                    $query->select('id', 'name', 'avatar');
                },
                'usersWaitingBook' => function($query) {
                    $query->select('id', 'name', 'avatar');
                    $query->orderBy('book_user.created_at', 'ASC');
                },
                'category' => function($query) {
                    $query->select('id', 'name');
                },
                'office' => function($query) {
                    $query->select('id', 'name');
                },
                'owner' => function($query) {
                    $query->select('id', 'name');
                },
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }
}