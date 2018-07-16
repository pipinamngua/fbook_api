<?php

namespace App\Repositories;

use App\Eloquent\Book;
use App\Eloquent\Media;
use App\Eloquent\Owner;
use App\Eloquent\User;
use App\Eloquent\Office;
use App\Eloquent\BookUser;
use App\Eloquent\UpdateBook;
use App\Eloquent\Notification;
use App\Contracts\Repositories\BookRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\MediaRepository;
use App\Contracts\Repositories\LogReputationRepository;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\Repositories\UploadableTrait;
use App\Events\NotificationHandler;
use App\Exceptions\Api\ActionException;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknownException;
use Carbon\Carbon;
use Log;

class BookRepositoryEloquent extends AbstractRepositoryEloquent implements BookRepository
{
    use UploadableTrait;

    protected $userSelect = [
        'id',
        'name',
        'email',
        'phone',
        'code',
        'position',
        'role',
        'office_id',
        'avatar',
        'tags',
    ];

    public function model()
    {
        return new Book;
    }

    public function updateBookModel()
    {
        return new UpdateBook;
    }

    public function getDataInHomepage($with = [], $dataSelect = ['*'], $officeId = '')
    {
        $limit = config('paginate.book_home_limit');

        return [
        [
            'key' => config('model.filter_books.latest.key'),
            'title' => config('model.filter_books.latest.title'),
            'data' => $this->getLatestBooks($with, $dataSelect, $limit, [], $officeId)->items(),
        ],
        [
            'key' => config('model.filter_books.view.key'),
            'title' => config('model.filter_books.view.title'),
            'data' => $this->getBooksByCountView($with, $dataSelect, $limit, [], $officeId)->items(),
        ],
        [
            'key' => config('model.filter_books.rating.key'),
            'title' => config('model.filter_books.rating.title'),
            'data' => $this->getBooksByRating($with, $dataSelect, $limit, [], $officeId)->items(),
        ],
        [
            'key' => config('model.filter_books.waiting.key'),
            'title' => config('model.filter_books.waiting.title'),
            'data' => $this->getBooksByWaiting($with, $dataSelect, $limit, [], $officeId)->items(),
        ],
        [
            'key' => config('model.filter_books.read.key'),
            'title' => config('model.filter_books.read.title'),
            'data' => $this->getBooksByRead($with, $dataSelect, $limit, [], $officeId)->items(),
        ],
        ];
    }

    public function getDataFilterInHomepage($with = [], $dataSelect = ['*'], $attribute = [], $officeId = '')
    {
        $limit = config('paginate.book_home_limit');

        return [
        [
            'key' => config('model.filter_books.latest.key'),
            'title' => config('model.filter_books.latest.title'),
            'data' => $this->getLatestBooks($with, $dataSelect, $limit, $attribute, $officeId)->items(),
        ],
        [
            'key' => config('model.filter_books.view.key'),
            'title' => config('model.filter_books.view.title'),
            'data' => $this->getBooksByCountView($with, $dataSelect, $limit, $attribute, $officeId)->items(),
        ],
        [
            'key' => config('model.filter_books.rating.key'),
            'title' => config('model.filter_books.rating.title'),
            'data' => $this->getBooksByRating($with, $dataSelect, $limit, $attribute, $officeId)->items(),
        ],
        [
            'key' => config('model.filter_books.waiting.key'),
            'title' => config('model.filter_books.waiting.title'),
            'data' => $this->getBooksByWaiting($with, $dataSelect, $limit, $attribute, $officeId)->items(),
        ],
        [
            'key' => config('model.filter_books.read.key'),
            'title' => config('model.filter_books.read.title'),
            'data' => $this->getBooksByRead($with, $dataSelect, $limit, $attribute, $officeId)->items(),
        ],
        ];
    }

    public function getDataSearch(array $attribute, $with = [], $dataSelect = ['*'], $officeId = '')
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
        ->select($dataSelect)
        ->with($with)
        ->where(function ($query) use ($attribute, $officeId) {
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
                $query->where(function ($query) use ($attribute) {
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
        ->getBookByOffice($officeId)
        ->orderBy($input['sort']['field'], $input['sort']['type'])
        ->paginate(config('paginate.default'));
    }

    public function getDataSearchTitle(array $attribute, $with = [], $dataSelect = ['*'], $officeId = '')
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
        ->select($dataSelect)
        ->with($with)
        ->where(function ($query) use ($attribute, $officeId) {
            if (isset($attribute['search']['keyword']) && $attribute['search']['keyword']) {
                $query->where('title', 'LIKE', '%' . $attribute['search']['keyword'] . '%');
            }
        })
        ->getBookByOffice($officeId)
        ->orderBy($input['sort']['field'], $input['sort']['type'])
        ->paginate(config('paginate.default'));
    }

    public function getDataSearchAuthor(array $attribute, $with = [], $dataSelect = ['*'], $officeId = '')
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
        ->select($dataSelect)
        ->with($with)
        ->where(function ($query) use ($attribute, $officeId) {
            if (isset($attribute['search']['keyword']) && $attribute['search']['keyword']) {
                $query->where('author', 'LIKE', '%' . $attribute['search']['keyword'] . '%');
            }
        })
        ->getBookByOffice($officeId)
        ->orderBy($input['sort']['field'], $input['sort']['type'])
        ->paginate(config('paginate.default'));
    }

    public function getDataSearchDescription(array $attribute, $with = [], $dataSelect = ['*'], $officeId = '')
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
        ->select($dataSelect)
        ->with($with)
        ->where(function ($query) use ($attribute, $officeId) {
            if (isset($attribute['search']['keyword']) && $attribute['search']['keyword']) {
                $query->where('description', 'LIKE', '%' . $attribute['search']['keyword'] . '%');
            }
        })
        ->getBookByOffice($officeId)
        ->orderBy($input['sort']['field'], $input['sort']['type'])
        ->paginate(config('paginate.default'));
    }

    protected function getLatestBooks($with = [], $dataSelect = ['*'], $limit = '', $attribute = [], $officeId = '')
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
        ->select($dataSelect)
        ->with($with)
        ->getData(config('model.filter_books.latest.field'), $input['filters'])
        ->getBookByOffice($officeId)
        ->orderBy($input['sort']['field'], $input['sort']['type'])
        ->paginate($limit ?: config('paginate.default'));
    }

    protected function getLatestBooksInDetail($with = [], $dataSelect = ['*'], $limit = '', $attribute = [], $officeId = '')
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
        ->select($dataSelect)
        ->with($with)
        ->getBookByOffice($officeId)
        ->orderBy($input['sort']['field'], $input['sort']['type'])
        ->paginate($limit ?: config('paginate.default'));
    }

    protected function getBooksByCountView($with = [], $dataSelect = ['*'], $limit = '', $attribute = [], $officeId = '')
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
        ->select($dataSelect)
        ->with($with)
        ->getData(config('model.filter_books.view.field'), $input['filters'])
        ->getBookByOffice($officeId)
        ->orderBy($input['sort']['field'], $input['sort']['type'])
        ->paginate($limit ?: config('paginate.default'));
    }

    protected function getBooksByCountViewInDetail($with = [], $dataSelect = ['*'], $limit = '', $attribute = [], $officeId = '')
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
        ->select($dataSelect)
        ->with($with)
        ->getData(config('model.filter_books.view.field'), $input['filters'])
        ->getBookByOffice($officeId)
        ->orderBy($input['sort']['field'], $input['sort']['type'])
        ->paginate($limit ?: config('paginate.default'));
    }

    protected function getBooksByRating($with = [], $dataSelect = ['*'], $limit = '', $attribute = [], $officeId = '')
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
        ->select($dataSelect)
        ->with($with)
        ->getData(config('model.filter_books.rating.field'), $input['filters'])
        ->getBookByOffice($officeId)
        ->orderBy($input['sort']['field'], $input['sort']['type'])
        ->paginate($limit ?: config('paginate.default'));
    }

    protected function getBooksByRatingInDetail($with = [], $dataSelect = ['*'], $limit = '', $attribute = [], $officeId = '')
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
        ->select($dataSelect)
        ->with($with)
        ->getData(config('model.filter_books.rating.field'), $input['filters'])
        ->getBookByOffice($officeId)
        ->orderBy($input['sort']['field'], $input['sort']['type'])
        ->paginate($limit ?: config('paginate.default'));
    }

    protected function getBooksByBookUserStatus(
        $status,
        $with = [],
        $dataSelect = ['*'],
        $limit = '',
        $attribute = [],
        $officeId = ''
    ) {
        $input = $this->getDataInput($attribute);

        $numberOfUserWaitingBook = \DB::table('books')
        ->join('book_user', 'books.id', '=', 'book_user.book_id')
        ->select('book_user.book_id', \DB::raw('count(book_user.user_id) as count_waiting'))
        ->where('book_user.status', $status)
        ->groupBy('book_user.book_id')
        ->orderBy('count_waiting', 'DESC')
        ->limit($limit ?: config('paginate.default'))
        ->get();

        $books = $this->model()
        ->select($dataSelect)
        ->with($with)
        ->whereIn('id', $numberOfUserWaitingBook->pluck('book_id')->toArray())
        ->getData($input['sort']['field'], $input['filters'], $input['sort']['type'])
        ->getBookByOffice($officeId)
        ->paginate($limit ?: config('paginate.default'));

        foreach ($books->items() as $book) {
            $book->count_waiting = $numberOfUserWaitingBook->where('book_id', $book->id)->first()->count_waiting;
        }

        return $books;
    }

    protected function getBooksByWaiting($with = [], $dataSelect = ['*'], $limit = '', $attribute = [], $officeId = '')
    {
        return $this->getBooksByBookUserStatus(
            config('model.book_user.status.waiting'),
            $with,
            $dataSelect,
            $limit,
            $attribute,
            $officeId
        );
    }

    protected function getBooksByRead($with = [], $dataSelect = ['*'], $limit = '', $attribute = [], $officeId = '')
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

    protected function getDataInputCountView($attribute = [])
    {
        $sort = [
        'field' => 'count_view',
        'type' => 'desc'
        ];
        $filters = [];

        if (isset($attribute['sort']['by']) && $attribute['sort']['by']) {
            $sort['field'] = $attribute['sort']['by'];
        }

        if (isset($attribute['sort']['order_by']) && $attribute['sort']['order_by']) {
            $sort['type'] = $attribute['sort']['order_by'];
        }

        if (isset($attribute['filters']) && $attribute['filters']) {
            $filters = $attribute['filters'];
        }

        return compact('sort', 'filters');
    }

    protected function getDataInputRating($attribute = [])
    {
        $sort = [
        'field' => 'avg_star',
        'type' => 'desc'
        ];
        $filters = [];

        if (isset($attribute['sort']['by']) && $attribute['sort']['by']) {
            $sort['field'] = $attribute['sort']['by'];
        }

        if (isset($attribute['sort']['order_by']) && $attribute['sort']['order_by']) {
            $sort['type'] = $attribute['sort']['order_by'];
        }

        if (isset($attribute['filters']) && $attribute['filters']) {
            $filters = $attribute['filters'];
        }

        return compact('sort', 'filters');
    }

    public function getBooksByFields($with = [], $dataSelect = ['*'], $field, $attribute = [], $officeId = '')
    {
        switch ($field) {
            case config('model.filter_books.view.key'):
                return $this->getBooksByCountViewInDetail($with, $dataSelect, '', $attribute, $officeId);

            case config('model.filter_books.latest.key'):
                return $this->getLatestBooksInDetail($with, $dataSelect, '', $attribute, $officeId);

            case config('model.filter_books.rating.key'):
                return $this->getBooksByRatingInDetail($with, $dataSelect, '', $attribute, $officeId);

            case config('model.filter_books.waiting.key'):
                return $this->getBooksByWaiting($with, $dataSelect, '', $attribute, $officeId);

            case config('model.filter_books.read.key'):
                return $this->getBooksByRead($with, $dataSelect, '', $attribute, $officeId);
        }
    }

    public function booking(Book $book, array $attributes)
    {
        $ownerId = $attributes['item']['owner_id'];
        if ($ownerId == $this->user->id) {
            throw new ActionException('not_booking_book_owned');
        }
        $checkUser = $book->users()
        ->wherePivot('user_id', $this->user->id)
        ->wherePivot('owner_id', $ownerId)
        ->orderBy('book_user.created_at', 'desc')
        ->first();
        if ($checkUser) {
            if ($checkUser->pivot->status == config('model.book_user.status.reading')
            && (
                $attributes['item']['status'] == config('model.book_user.status.returning')
                || $attributes['item']['status'] == config('model.book_user.status.returned')
            )
            ) {
                $book->users()
                    ->wherePivot('owner_id', $ownerId)
                    ->wherePivot('status', config('model.book_user.status.reading'))
                    ->updateExistingPivot($this->user->id, [
                    'status' => config('model.book_user.status.returning'),
                    ]);
                Event::fire('androidNotification', config('model.notification.returning'));
                $message = sprintf(translate('notification.returing_book'), $this->user->name, $book->title);
                event(new NotificationHandler($message, $ownerId, config('model.notification.returning')));
                Event::fire('notification', [
                    [
                        'current_user_id' => $this->user->id,
                        'get_user_id' => $ownerId,
                        'target_id' => $book->id,
                        'type' => config('model.notification.returning'),
                    ]
                ]);
            } elseif ($checkUser->pivot->status == config('model.book_user.status.waiting')
            && $attributes['item']['status'] == config('model.book_user_status_cancel')
            ) {
                $book->users()
                    ->wherePivot('status', config('model.book_user.status.waiting'))
                    ->detach($this->user->id);
                Event::fire('androidNotification', config('model.notification.cancel'));
                $message = sprintf(translate('notification.cancel_book_borrowing'), $this->user->name, $book->title);
                event(new NotificationHandler($message, $ownerId, config('model.notification.cancel')));
                Event::fire('notification', [
                    [
                    'current_user_id' => $this->user->id,
                    'get_user_id' => $ownerId,
                    'target_id' => $book->id,
                    'type' => config('model.notification.cancel'),
                    ]
                ]);
            } elseif ($checkUser->pivot->status == config('model.book_user.status.returned')
            && $attributes['item']['status'] == config('model.book_user.status.waiting')
            ) {
                $book->users()->attach($this->user->id, [
                    'status' => config('model.book_user.status.waiting'),
                    'owner_id' => $ownerId,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                Event::fire('androidNotification', config('model.notification.waiting'));
                $message = sprintf(translate('notification.waiting_book_borrowing'), $this->user->name, $book->title);
                event(new NotificationHandler($message, $ownerId, config('model.notification.waiting')));
                Event::fire('notification', [
                    [
                    'current_user_id' => $this->user->id,
                    'get_user_id' => $ownerId,
                    'target_id' => $book->id,
                    'type' => config('model.notification.waiting'),
                    ]
                ]);
            }
        } else {
            $book->users()->attach($this->user->id, [
            'status' => config('model.book_user.status.waiting'),
            'owner_id' => $ownerId,
            'days_to_read' => $attributes['item']['days_to_read'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ]);
            Event::fire('androidNotification', config('model.notification.waiting'));
            $message = sprintf(translate('notification.waiting_book_borrowing'), $this->user->name, $book->title);
            event(new NotificationHandler($message, $ownerId, config('model.notification.waiting')));
            Event::fire('notification', [
            [
                'current_user_id' => $this->user->id,
                'get_user_id' => $ownerId,
                'target_id' => $book->id,
                'type' => config('model.notification.waiting'),
            ]
            ]);
        }
    }

    public function review($bookId, array $data)
    {
        try {
            $book = $this->model()->findOrFail($bookId);
            $dataReview = array_only($data, ['content', 'star']);
            $dataReview['created_at'] = $dataReview['updated_at'] = Carbon::now();

            $book->reviews()->detach($this->user->id);
            $book->reviews()->attach([
            $this->user->id => $dataReview
            ]);
            $ownersId = $book->owners()->pluck('id');
            if (isset($dataReview['star'])) {
                Event::fire('books.averageStar', [
                    [
                    'book' => $book,
                    'star' => $dataReview['star'],
                    ]
                ]);

                foreach ($ownersId as $ownerId) {
                    Event::fire('androidNotification', config('model.notification.review'));
                    $message = sprintf(translate('notification.reviewed_book'), $this->user->name, $book->title);
                    event(new NotificationHandler($message, $ownerId, config('model.notification.review')));
                    Event::fire('notification', [
                        [
                            'current_user_id' => $this->user->id,
                            'get_user_id' => $ownerId,
                            'target_id' => $book->id,
                            'type' => config('model.notification.review'),
                        ]
                    ]);
                }
            }
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }

    public function reviewNew($bookId, array $data)
    {
        try {
            $book = $this->model()->findOrFail($bookId);
            $dataReview = array_only($data, ['title', 'content', 'star']);
            $dataReview['created_at'] = $dataReview['updated_at'] = Carbon::now();

            $book->reviews()->detach($this->user->id);
            $book->reviews()->attach([
            $this->user->id => $dataReview
            ]);
            $ownersId = $book->owners()->pluck('id');
            if (isset($dataReview['star'])) {
                Event::fire('books.averageStar', [
                    [
                    'book' => $book,
                    'star' => $dataReview['star'],
                    ]
                ]);

                foreach ($ownersId as $ownerId) {
                    Event::fire('androidNotification', config('model.notification.review'));
                    $message = sprintf(translate('notification.reviewed_book'), $this->user->name, $book->title);
                    event(new NotificationHandler($message, $ownerId, config('model.notification.review')));
                    Event::fire('notification', [
                        [
                            'current_user_id' => $this->user->id,
                            'get_user_id' => $ownerId,
                            'target_id' => $book->id,
                            'type' => config('model.notification.review'),
                        ]
                    ]);
                }
            }
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }

    protected function getDataInput($attribute = [])
    {
        $sort = [
        'field' => 'created_at',
        'type' => 'desc'
        ];
        $filters = [];

        if (isset($attribute['sort']['by']) && $attribute['sort']['by']) {
            $sort['field'] = $attribute['sort']['by'];
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

            return $book->load(['media', 'reviewsDetail',
            'usersWaiting' => function ($query) {
                $query->select(array_merge($this->userSelect, ['owner_id']));
                $query->orderBy('book_user.created_at', 'ASC');
            },
            'usersReading' => function ($query) {
                $query->select(array_merge($this->userSelect, ['owner_id']));
                $query->orderBy('book_user.created_at', 'ASC');
            },
            'usersReturning' => function ($query) {
                $query->select(array_merge($this->userSelect, ['owner_id']));
                $query->orderBy('book_user.created_at', 'ASC');
            },
            'usersReturned' => function ($query) {
                $query->select(array_merge($this->userSelect, ['owner_id']));
                $query->orderBy('book_user.created_at', 'DESC');
            },
            'category' => function ($query) {
                $query->select('id', 'name_vi', 'name_en', 'name_jp');
            },
            'office' => function ($query) {
                $query->select('id', 'name');
            },
            'owners' => function ($query) {
                $query->select($this->userSelect);
            }
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }

    public function increaseView(Book $book)
    {
        $book->increment('count_view');
    }

    protected function uploadAndSaveMediasForBook(array $medias, Book $book, MediaRepository $mediaRepository)
    {
        $dataMedias = [];

        foreach ($medias as $media) {
            $dataMedias[] = array_only($media, ['file', 'type']);
        }

        $mediaRepository->uploadAndSaveMedias(
            $book,
            $dataMedias,
            strtolower(class_basename($this->model()))
        );
    }

    protected function updateMediasForBook(array $medias, Book $book, MediaRepository $mediaRepository)
    {
        $dataMedias = [];

        foreach ($medias as $media) {
            $dataMedias[] = array_only($media, ['file', 'id']);
        }

        $mediaRepository->updateMedias(
            $book,
            $dataMedias,
            strtolower(class_basename($this->model()))
        );
    }

    protected function uploadAndSaveMediasForUpdateBook(array $medias, UpdateBook $updateBook, MediaRepository $mediaRepository)
    {
        $dataMedias = [];

        foreach ($medias as $media) {
            $dataMedias[] = array_only($media, ['file', 'type']);
        }

        $mediaRepository->uploadAndSaveEditMedias(
            $updateBook,
            $dataMedias,
            strtolower(class_basename($this->model()))
        );
    }

    protected function updateMediasForUpdateBook(array $medias, UpdateBook $updateBook, MediaRepository $mediaRepository)
    {
        $dataMedias = [];

        foreach ($medias as $media) {
            $dataMedias[] = array_only($media, ['file', 'id', 'type']);
        }

        $mediaRepository->updateEditMedias(
            $updateBook,
            $dataMedias,
            strtolower(class_basename($this->model()))
        );
    }

    protected function getBookByCode(string $code)
    {
        return $this->model()->whereCode($code)->first();
    }

    private function addOwnerBook(Book $book, LogReputationRepository $logReputationRepository)
    {
        $book->owners()->detach($this->user->id);
        $book->owners()->attach($this->user->id, [
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
        ]);
        $userRepository = new UserRepositoryEloquent();
        $logData['user_id'] = $this->user->id;
        $logData['book_id'] = $book->id;

        $logData = json_encode($logData);
        $userRepository->addReputation($this->user->id, config('model.reputation.share_book'), $logData, config('model.log_type.share_book'), $logReputationRepository);
    }

    public function store(array $attributes, MediaRepository $mediaRepository, LogReputationRepository $logReputationRepository)
    {
        if (!isset($attributes['medias'])) {
            $dataCompareBook = array_only($attributes, [
            'title',
            'description',
            'author',
            'publish_date',
            'category_id',
            'office_id',
            ]);
            $bookExistedInDatabase = $this->model()->where($dataCompareBook)->first();

            if (count($bookExistedInDatabase)) {
                $this->addOwnerBook($bookExistedInDatabase, $logReputationRepository);

                return $bookExistedInDatabase->load('category', 'office', 'media');
            }
        }

        $dataBook = array_only($attributes, $this->model()->getFillable());
        $dataBook['code'] = sha1(time());
        $book = $this->model()->create($dataBook);

        $this->addOwnerBook($book, $logReputationRepository);

        if (isset($attributes['medias'])) {
            $this->uploadAndSaveMediasForBook($attributes['medias'], $book, $mediaRepository);
        }
        Event::fire('notification', [
        [
            'current_user_id' => $this->user->id,
            'get_user_id' => config('model.notification.add_book'),
            'target_id' => $book->id,
            'type' => config('model.notification.add_owner'),
        ]
        ]);

        return $book->load('category', 'office', 'media');
    }

    public function destroy(Book $book)
    {
        $book->delete();
    }

    public function getBookByCategory($categoryId, $dataSelect = ['*'], $with = [], $officeId = '')
    {
        return $this->select($dataSelect)->with($with)
        ->getBookByOffice($officeId)
        ->where('category_id', $categoryId)
        ->paginate(config('paginate.default'));
    }

    public function getBookFilteredByCategory($categoryId, $attribute = [], $dataSelect = ['*'], $with = [], $officeId = '')
    {
        $input = $this->getDataInput($attribute);

        return $this->model()
        ->select($dataSelect)
        ->with($with)
        ->where('category_id', $categoryId)
        ->getData($input['sort']['field'], $input['filters'], $input['sort']['type'])
        ->getBookByOffice($officeId)
        ->paginate(config('paginate.default'));
    }

    public function addOwner($id, LogReputationRepository $logReputationRepository)
    {
        try {
            $book = $this->model()->findOrFail($id);
            $bookOwner = $book->checkAddedOwner()->where('user_id', $this->user->id)->first();
            if ($bookOwner) {
                $bookOwner->pivot->added = config('model.status_owner.added');
                $bookOwner->pivot->updated_at = Carbon::now();
                $bookOwner->pivot->deleted_at = null;
                $bookOwner->pivot->save();
            } elseif (!$bookOwner) {
                $book->checkAddedOwner()->attach($this->user->id, [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                ]);
                $logData['user_id'] = $this->user->id;
                $logData['book_id'] = $book->id;

                $logData = json_encode($logData);

                $userRepository = new UserRepositoryEloquent();

                $userRepository->addReputation($this->user->id, config('model.reputation.add_owner'), $logData, config('model.log_type.share_book'), $logReputationRepository);
            } else {
                throw new ActionException('ownered_current_book');
            }
            Event::fire('notification', [
            [
                'current_user_id' => $this->user->id,
                'get_user_id' => config('model.notification.add_book'),
                'target_id' => $book->id,
                'type' => config('model.notification.add_owner'),
            ]
            ]);

            return $bookOwner;
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }

    public function removeOwner(Book $book)
    {
        // Delete requests of borrowing book
        $book->users()
        ->wherePivot('owner_id', $this->user->id)
        ->wherePivot('status', '<>', config('model.book_user.status.returned'))
        ->detach();

        // Soft delete (can not use original soft delete for pivot)
        $bookOwner = $book->checkAddedOwner()->where('user_id', $this->user->id)->first();
        $bookOwner->pivot->deleted_at = Carbon::now();
        $bookOwner->pivot->save();

        Event::fire('notification', [
        [
            'current_user_id' => $this->user->id,
            'get_user_id' => config('model.notification.remove_book'),
            'target_id' => $book->id,
            'type' => config('model.notification.remove_owner'),
        ]
        ]);
        return $bookOwner;
    }

    public function uploadMedia(Book $book, $attributes = [], MediaRepository $mediaRepository)
    {
        $this->uploadAndSaveMediasForBook($attributes['medias'], $book, $mediaRepository);

        return $book->load('category', 'office', 'media');
    }

    public function checkApprove(Book $book, $attribute = [])
    {
        $userId = $attribute['user_id'];
        $approve = $book->users()
        ->select('approved')
        ->wherePivot('user_id', $userId)
        ->wherePivot('owner_id', $this->user->id)
        ->first();

        return $approve;
    }

    public function approve(Book $book, $attribute = [])
    {
        $userId = $attribute['user_id'];
        $key = $attribute['key'];
        $ownerBook = $book->owners()->where('user_id', $this->user->id)->first();
        if ($key == config('settings.book_key.approve')) {
            if ($ownerBook->pivot->status == config('model.book.status.available')) {
                $waitingList = $book->usersWaiting()
                ->where('user_id', $userId)
                ->wherePivot('owner_id', $this->user->id)
                ->count();
                if ($waitingList) {
                    $book->owners()->updateExistingPivot($this->user->id, [
                    'status' => config('model.book.status.unavailable'),
                    ]);
                    $book->users()
                        ->wherePivot('owner_id', $this->user->id)
                        ->wherePivot('status', config('model.book_user.status.waiting'))
                        ->updateExistingPivot($userId, [
                        'status' => config('model.book_user.status.reading'),
                        ]);
                    $book->users()
                        ->wherePivot('owner_id', $this->user->id)
                        ->updateExistingPivot($userId, [
                        'approved' => config('model.book_user.approved.approved_before'),
                        ]);
                    $book->users()
                        ->wherePivot('owner_id', '<>', $this->user->id)
                        ->wherePivot('status', config('model.book_user.status.waiting'))
                        ->detach($userId);
                    Event::fire('androidNotification', config('model.notification.approve_waiting'));
                    $message = sprintf(translate('notification.accepted_book'), $this->user->name, $book->title);
                    event(new NotificationHandler($message, $userId, config('model.notification.approve_waiting')));
                    Event::fire('notification', [
                        [
                        'current_user_id' => $this->user->id,
                        'get_user_id' => $userId,
                        'target_id' => $book->id,
                        'type' => config('model.notification.approve_waiting'),
                        ]
                    ]);
                } else {
                    throw new ActionException('not_in_waiting_list');
                }
            } else {
                $returningList = $book->usersReturning()
                ->where('user_id', $userId)
                ->wherePivot('owner_id', $this->user->id)
                ->count();
                if ($returningList) {
                    $book->owners()->updateExistingPivot($this->user->id, [
                    'status' => config('model.book.status.available'),
                    ]);
                    $book->users()
                        ->wherePivot('owner_id', $this->user->id)
                        ->wherePivot('status', config('model.book_user.status.returning'))
                        ->updateExistingPivot($userId, [
                        'status' => config('model.book_user.status.returned'),
                        ]);
                    Event::fire('androidNotification', config('model.notification.approve_returning'));
                    $message = sprintf(translate('notification.approve_returning_book'), $this->user->name, $book->title);
                    event(new NotificationHandler($message, $userId, config('model.notification.approve_returning')));
                    Event::fire('notification', [
                        [
                        'current_user_id' => $this->user->id,
                        'get_user_id' => $userId,
                        'target_id' => $book->id,
                        'type' => config('model.notification.approve_returning'),
                        ]
                    ]);
                } else {
                    throw new ActionException('data_invalid');
                }
            }
        } elseif ($key == config('settings.book_key.unapprove')) {
            if ($ownerBook->pivot->status == config('model.book.status.available')) {
                $returnedList = $book->usersReturned()
                ->where('user_id', $userId)
                ->wherePivot('owner_id', $this->user->id)
                ->count();
                if ($returnedList) {
                    $book->owners()->updateExistingPivot($this->user->id, [
                    'status' => config('model.book.status.unavailable'),
                    ]);
                    $book->users()->updateExistingPivot($userId, [
                        'status' => config('model.book_user.status.returning'),
                    ]);
                } else {
                    throw new ActionException('data_invalid');
                }
            } else {
                $readingList = $book->usersReading()
                ->where('user_id', $userId)
                ->wherePivot('owner_id', $this->user->id)
                ->count();
                if ($readingList) {
                    $book->owners()->updateExistingPivot($this->user->id, [
                    'status' => config('model.book.status.available'),
                    ]);
                    $book->users()->updateExistingPivot($userId, [
                        'status' => config('model.book_user.status.waiting'),
                    ]);
                    Event::fire('androidNotification', config('model.notification.unapprove_waiting'));
                    $message = '' . $this->user->name . ' unapprove waiting book: ' . $book->title;
                    event(new NotificationHandler($message, $userId, config('model.notification.unapprove_waiting')));
                    Event::fire('notification', [
                        [
                        'current_user_id' => $this->user->id,
                        'get_user_id' => $userId,
                        'target_id' => $book->id,
                        'type' => config('model.notification.unapprove_waiting'),
                        ]
                    ]);
                } else {
                    throw new ActionException('data_invalid');
                }
            }
        } elseif ($key == config('settings.book_key.remove_waiting')) {
            $book->users()
            ->wherePivot('status', config('model.book_user.status.waiting'))
            ->detach($userId);
        } else {
            throw new ActionException('data_invalid');
        }
    }

    public function getBookByOffice($officeId, $dataSelect = ['*'], $with = [])
    {
        return $this->select($dataSelect)
        ->with($with)
        ->where('office_id', $officeId)
        ->paginate(config('paginate.default'));
    }

    public function requestUpdateBook(array $attributes, Book $book, MediaRepository $mediaRepository)
    {
        $dataBook = array_only($attributes, $this->updateBookModel()->getFillable());
        $bookWithCurrentCode = $this->getBookByCode($attributes['code']);

        if ($bookWithCurrentCode && $bookWithCurrentCode->id != $book->id) {
            throw new ActionException(__FUNCTION__);
        }

        $dataBook['user_id'] = $this->user->id;

        $bookRequestId = $book->updateBooks()->create($dataBook)->id;

        $bookRequest = app(UpdateBook::class)->find($bookRequestId);
        if ($bookRequest) {
            if (isset($attributes['update'])) {
                $this->updateMediasForUpdateBook($attributes['update'], $bookRequest, $mediaRepository);
            }

            if (isset($attributes['medias'])) {
                $this->uploadAndSaveMediasForUpdateBook($attributes['medias'], $bookRequest, $mediaRepository);
            }
        }

        foreach (config('settings.email_admin') as $admin) {
            $user_admin = app(User::class)->where('email', $admin)->first();
            if ($user_admin) {
                $user_admin_id = $user_admin->id;
                Event::fire('androidNotification', config('model.notification.admin.request_edit_book'));
                $message = sprintf(translate('notification.request_edit_book'), $this->user->name, $book->title);
                event(new NotificationHandler($message, $user_admin_id, config('model.notification.admin.request_edit_book')));
                Event::fire('notification', [
                [
                    'current_user_id' => $this->user->id,
                    'get_user_id' => $user_admin_id,
                    'target_id' => $book->id,
                    'type' => config('model.notification.admin.request_edit_book'),
                ]
                ]);
            }
        }
    }

    public function approveRequestUpdateBook($updateBookId)
    {
        try {
            $updateBook = app(UpdateBook::class)->findOrFail($updateBookId);
            $dataBookUpdate = [
            'title' => $updateBook['title'],
            'description' => $updateBook['description'],
            'author' => $updateBook['author'],
            'publish_date' => $updateBook['publish_date'],
            'category_id' => $updateBook['category_id'],
            'office_id' => $updateBook['office_id']
            ];

            $updateBook->currentBookInfo()->update($dataBookUpdate);
            $currentBook = $updateBook->currentBookInfo;

            foreach ($updateBook->updateMedia as $updateMedia) {
                if ($updateMedia['media_id'] != null) {
                    $dataFile = [
                    'name' => $updateMedia['name'],
                    'size' => $updateMedia['size'],
                    'type' => $updateMedia['type'],
                    'path' => $updateMedia['path']
                    ];

                    $currentMedia = $currentBook->media()->findOrFail($updateMedia['media_id']);
                    if ($currentMedia) {
                        if (isset($dataFile)) {
                            $this->destroyFile($currentMedia->path);
                            $currentMedia->update($dataFile);
                        }
                    }

                    $dataFile = [];
                } else {
                    $dataFile = [
                    'name' => $updateMedia['name'],
                    'size' => $updateMedia['size'],
                    'type' => $updateMedia['type'],
                    'path' => $updateMedia['path']
                    ];

                    $currentBook->media()->create($dataFile);
                    $dataFile = [];
                }
            }
            Event::fire('androidNotification', config('model.notification.admin.approve_request_update_book'));
            $message = sprintf(translate('notification.approve_request_update_book'), $this->user->name, $updateBook->title);
            event(new NotificationHandler($message, $updateBook->user_id, config('model.notification.admin.approve_request_update_book')));
            Event::fire('notification', [
            [
                'current_user_id' => $this->user->id,
                'get_user_id' => $updateBook->user_id,
                'target_id' => $updateBook->book_id,
                'type' => config('model.notification.admin.approve_request_update_book'),
            ]
            ]);

            $updateBook->delete();
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }

    public function deleteRequestUpdateBook($updateBookId)
    {
        try {
            $updateBook = app(UpdateBook::class)->findOrFail($updateBookId);

            Event::fire('androidNotification', config('model.notification.admin.delete_request_update_book'));
            $message = sprintf(translate('notification.delete_request_edit_book'), $this->user->name, $updateBook->title);
            event(new NotificationHandler($message, $updateBook->user_id, config('model.notification.admin.delete_request_update_book')));
            Event::fire('notification', [
            [
                'current_user_id' => $this->user->id,
                'get_user_id' => $updateBook->user_id,
                'target_id' => $updateBook->book_id,
                'type' => config('model.notification.admin.delete_request_update_book'),
            ]
            ]);

            foreach ($updateBook->updateMedia as $updateMedia) {
                $this->destroyFile($updateMedia->path);
            }

            $updateBook->delete();
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }

    public function countRecord()
    {
        return $this->model()->count();
    }

    public function getByPage($dataSelect = ['*'], $withRelation = [])
    {
        return $this->model()
        ->select($dataSelect)
        ->with(array_merge($withRelation, ['office', 'image']))
        ->withCount('owners')
        ->orderBy('created_at', 'ASC')
        ->paginate(config('paginate.default'));
    }

    public function searchBook($data, $dataSelect = ['*'], $withRelation = [])
    {
        Paginator::currentPageResolver(function () use ($data) {
            return $data['page'];
        });
        if ($data['type'] == config('model.filter_book.by_title')) {
            $query = $this->model()
            ->where('title', 'like', '%' . $data['key'] . '%');
        } else {
            $query = $this->model()
            ->where('author', 'like', '%' . $data['key'] . '%');
        }

        return $query->select($dataSelect)
        ->with(array_merge($withRelation, ['office']))
        ->withCount('owners')
        ->orderBy('created_at', 'ASC')
        ->paginate(config('paginate.default'));
    }

    public function countHaveBook()
    {
        return $this->model()->get()->count();
    }

    public function destroyBook(Book $deleteBook)
    {
        $deleteBook->delete();
    }

    public function storeBookOffice(Book $book, $officeIdUserCurrent, LogReputationRepository $logReputationRepository)
    {
        
        // Khi nguoi dung chua co quyen sach nao nhu nay thi tao moi trong bang books, owners, media --> tat ca deu copy ban ghi cu sang
        $dataBook = [
            'title' => $book->title,
            'description' => $book->description,
            'author' => $book->author,
            'publish_date' => $book->publish_date,
            'category_id' => $book->category_id,
            'office_id' => $officeIdUserCurrent,
            'code' => sha1(time()),
        ];
        $medias = $book->media()->get();
        $bookNew = $this->model()->create($dataBook);

        foreach ($medias as $media) {
            $this->saveImageBookCurrent($media, $bookNew->id);
        }

        $this->addOwnerBook($bookNew, $logReputationRepository);
        
        Event::fire('notification', [
        [
            'current_user_id' => $this->user->id,
            'get_user_id' => config('model.notification.add_book'),
            'target_id' => $bookNew->id,
            'type' => config('model.notification.add_owner'),
        ]
        ]);


        return $bookNew->load('category', 'office', 'media');

        // if (!isset($attributes['medias'])) {
        //     $dataCompareBook = array_only($attributes, [
        //         'title',
        //         'description',
        //         'author',
        //         'publish_date',
        //         'category_id',
        //         'office_id',
        //     ]);
        //     $bookExistedInDatabase = $this->model()->where($dataCompareBook)->first();

        //     if (count($bookExistedInDatabase)) {
        //         $this->addOwnerBook($bookExistedInDatabase, $logReputationRepository);

        //         return $bookExistedInDatabase->load('category', 'office', 'media');
        //     }
        // }

        // $dataBook = array_only($attributes, $this->model()->getFillable());
        // $dataBook['code'] = sha1(time());
        // $book = $this->model()->create($dataBook);

        // $this->addOwnerBook($book, $logReputationRepository);

        // if (isset($attributes['medias'])) {
        //     $this->uploadAndSaveMediasForBook($attributes['medias'], $book, $mediaRepository);
        // }
        // Event::fire('notification', [
        //     [
        //         'current_user_id' => $this->user->id,
        //         'get_user_id' => config('model.notification.add_book'),
        //         'target_id' => $book->id,
        //         'type' => config('model.notification.add_owner'),
        //     ]
        // ]);

        // return $book->load('category', 'office', 'media');
    }

    public function getListBookCurrentUser($book_name, $action = '')
    {
        $bookName = trim($book_name);
        
        if ($action == config('model.book_user.search.search_like')) {
            $books = User::findOrFail($this->user->id)->owners()->whereHas('owners', function ($query) use ($bookName) {
                $query->where('title', 'like', '%'. $bookName .'%');
            })->get();
        } else {
            $books = User::findOrFail($this->user->id)->owners()->whereHas('owners', function ($query) use ($bookName) {
                $query->where('title', $bookName);
            })->get();
        }

        return $books;
    }
    protected function saveImageBookCurrent(Media $media, $idBookCurrent)
    {
        $m = new Media();
        $dataMedia = [
            'name' => $media->name,
            'path' => $media->path,
            'size' => $media->size,
            'type' => $media->type,
            'target_id' => $idBookCurrent,
            'target_type' => $media->target_type,
        ];
        
        $m->create($dataMedia);
    }
}
