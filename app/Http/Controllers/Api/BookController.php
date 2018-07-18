<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\BookRepository;
use App\Contracts\Repositories\CategoryRepository;
use App\Contracts\Repositories\OfficeRepository;
use App\Contracts\Repositories\OwnerRepository;
use App\Contracts\Repositories\LogReputationRepository;
use App\Exceptions\Api\NotFoundException;
use App\Http\Requests\Api\Book\ApproveRequest;
use App\Http\Requests\Api\Book\BookFilteredByCategoryRequest;
use App\Http\Requests\Api\Book\BookFilterRequest;
use App\Http\Requests\Api\Book\FilterBookInCategoryRequest;
use App\Http\Requests\Api\Book\SearchRequest;
use App\Exceptions\Api\ActionException;
use App\Http\Requests\Api\Book\IndexRequest;
use App\Http\Requests\Api\Book\BookingRequest;
use App\Http\Requests\Api\Book\ReviewRequest;
use App\Http\Requests\Api\Book\StoreRequest;
use App\Contracts\Repositories\MediaRepository;
use App\Http\Requests\Api\Book\UpdateRequest;
use App\Http\Requests\Api\Book\UploadMediaRequest;
use Illuminate\Http\Request;
use App\Events\NotificationHandler;
use App\Contracts\Repositories\UserRepository;
use App\Eloquent\User;
use Log;
use App\Eloquent\Book;
use App\Exceptions\Api\UnknownException;

class BookController extends ApiController
{
    protected $select = [
        'id',
        'title',
        'description',
        'author',
        'publish_date',
        'total_page',
        'count_view',
        'category_id',
        'office_id',
        'avg_star',
    ];

    protected $ownerSelect = [
        'id',
        'name',
        'avatar',
        'position'
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
        'name_vi',
        'name_en',
        'name_jp',
    ];

    protected $officeSelect = [
        'id',
        'name',
    ];

    protected $counter;

    public function __construct(BookRepository $repository)
    {
        parent::__construct($repository);
    }

    public function index(IndexRequest $request)
    {
        $field = $request->input('field');
        $officeId = $request->get('office_id');

        if (!$field) {
            throw new ActionException;
        }

        $relations = [
            'owners' => function ($q) {
                $q->select($this->ownerSelect);
            },
            'category' => function ($q) {
                $q->select($this->categorySelect);
            },
            'image' => function ($q) {
                $q->select($this->imageSelect);
            },
            'office' => function ($q) {
                $q->select($this->officeSelect);
            }
        ];

        return $this->getData(function () use ($relations, $field, $officeId) {
            $data = $this->repository->getBooksByFields($relations, $this->select, $field, [], $officeId);

            $this->compacts['items'] = $this->reFormatPaginate($data);
        }, __FUNCTION__);
    }

    public function show($id)
    {
        $this->compacts['item'] = $this->repository->show($id);

        return $this->jsonRender();
    }

    public function store(
        StoreRequest $request,
        MediaRepository $mediaRepository,
        LogReputationRepository $logReputationRepository
    ) {
        $data = $request->all();

        return $this->doAction(function () use ($data, $mediaRepository, $logReputationRepository) {
            $this->compacts['item'] = $this->repository->store($data, $mediaRepository, $logReputationRepository);
        }, __FUNCTION__);
    }

    public function requestUpdate(UpdateRequest $request, $id, MediaRepository $mediaRepository)
    {
        $data = $request->all();

        return $this->doAction(function () use ($data, $id, $mediaRepository) {
            $book = $this->repository->findOrFail($id);
            $this->before('update', $book);

            $this->repository->requestUpdateBook($data, $book, $mediaRepository);
        }, __FUNCTION__);
    }

    public function approveRequestUpdate(Request $request, $updateBookId)
    {
        return $this->doAction(function () use ($updateBookId) {

            $this->repository->approveRequestUpdateBook($updateBookId);
        }, __FUNCTION__);
    }

    public function deleteRequestUpdate($updateBookId)
    {
        return $this->doAction(function () use ($updateBookId) {

            $this->repository->deleteRequestUpdateBook($updateBookId);
        }, __FUNCTION__);
    }

    public function increaseView($id)
    {
        return $this->doAction(function () use ($id) {
            $book = $this->repository->findOrFail($id);

            $this->repository->increaseView($book);
        }, __FUNCTION__);
    }

    public function destroy($id)
    {
        return $this->doAction(function () use ($id) {
            $book = $this->repository->findOrFail($id);
            $this->before('delete', $book);

            $this->repository->destroy($book);
        }, __FUNCTION__);
    }

    public function search(SearchRequest $request)
    {
        $data = $request->all();
        $officeId = $request->get('office_id');

        return $this->getData(function () use ($data, $officeId) {
            $this->compacts['titles'] = $this->reFormatPaginate(
                $this->repository->getDataSearchTitle($data, ['image', 'category', 'office', 'owners'], $this->select, $officeId)
            );
            $this->compacts['authors'] = $this->reFormatPaginate(
                $this->repository->getDataSearchAuthor($data, ['image', 'category', 'office', 'owners'], $this->select, $officeId)
            );
            $this->compacts['descriptions'] = $this->reFormatPaginate(
                $this->repository->getDataSearchDescription($data, ['image', 'category', 'office', 'owners'], $this->select, $officeId)
            );
            $this->compacts['users'] = $this->reFormatPaginate(
                $this->repository->getDataSearchUser($data)
            );
        });
    }

    public function searchAdmin(Request $request)
    {
        $data = $request->only(['key', 'type', 'page']);

        return $this->getData(function () use ($data) {
            $data = $this->repository->searchBook($data);

            $this->compacts['items'] = $this->reFormatPaginate($data);
        });
    }

    public function booking(BookingRequest $request)
    {
        $data = $request->all();

        return $this->doAction(function () use ($data) {
            $book = $this->repository->findOrfail($data['item']['book_id']);

            $this->repository->booking($book, $data);
        }, __FUNCTION__);
    }

    public function approve(
        $bookId,
        ApproveRequest $request,
        UserRepository $userRepository,
        LogReputationRepository $logReputationRepository
    ) {
        $data = $request->all();
        $key = $data['item']['key'];

        return $this->doAction(function () use ($data, $bookId, $key, $userRepository, $logReputationRepository) {
            $book = $this->repository->findOrfail($bookId);
            $this->before('update', $book);
            $check = $this->repository->checkApprove($book, $data['item']);

            $this->repository->approve($book, $data['item']);
            $this->compacts['log_id'] = $check;

            if ($key === config('settings.book_key.approve')) {
                if ($check->approved === config('model.book_user.approved.never_approve')) {
                    $logData['user_id'] = $data['item']['user_id'];
                    $logData['book_id'] = $book->id;
                    $logData['owners_id'] = $this->user->id;
                    $logData = json_encode($logData);

                    $userRepository->addReputation($this->user->id, config('model.reputation.approve_borrow'), $logData, config('model.log_type.approve_borrow'), $logReputationRepository);
                    $this->compacts['point'] = config('model.reputation.approve_borrow');
                }
            }
        }, __FUNCTION__);
    }

    public function sortBy()
    {
        $this->compacts['items'] = config('model.condition_sort_book');

        return $this->jsonRender();
    }

    public function review(ReviewRequest $request, $bookId)
    {
        $data = $request->item;

        return $this->doAction(function () use ($bookId, $data) {
            $this->repository->review($bookId, $data);
        }, __FUNCTION__);
    }

    public function reviewNew(ReviewRequest $request, $bookId)
    {
        $data = $request->item;

        return $this->doAction(function () use ($bookId, $data) {
            $this->repository->reviewNew($bookId, $data);
        }, __FUNCTION__);
    }

    public function filter(BookFilterRequest $request)
    {
        $field = $request->input('field');
        $officeId = $request->get('office_id');

        $input = $request->all();

        $relations = [
            'owners' => function ($q) {
                $q->select($this->ownerSelect);
            },
            'category' => function ($q) {
                $q->select($this->categorySelect);
            },
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

        return $this->getData(function () use ($relations, $field, $input, $officeId) {
            $data = $this->repository->getBooksByFields($relations, $this->select, $field, $input, $officeId);

            $this->compacts['items'] = $this->reFormatPaginate($data);
        });
    }

    public function category($categoryId, CategoryRepository $categoryRepository, Request $request)
    {
        $category = $categoryRepository->find($categoryId);
        $officeId = $request->get('office_id');

        if (!$category) {
            throw new NotFoundException;
        }

        $relations = [
            'owners' => function ($q) {
                $q->select($this->ownerSelect);
            },
            'category' => function ($q) {
                $q->select($this->categorySelect);
            },
            'image' => function ($q) {
                $q->select($this->imageSelect);
            },
            'office' => function ($q) {
                $q->select($this->officeSelect);
            }
        ];

        return $this->getData(function () use ($relations, $category, $officeId) {
            $bookCategory = $this->repository->getBookByCategory($category->id, $this->select, $relations, $officeId);
            $currentPage = $bookCategory->currentPage();

            $this->compacts['items'] = [
                'total' => $bookCategory->total(),
                'per_page' => $bookCategory->perPage(),
                'current_page' => $currentPage,
                'next_page' => ($bookCategory->lastPage() > $currentPage) ? $currentPage + 1 : null,
                'prev_page' => $currentPage - 1 ?: null,
                'category' => [
                    'id' => $category->id,
                    'name_vi' => $category->name_vi,
                    'name_en' => $category->name_en,
                    'name_jp' => $category->name_jp,
                    'data' => $bookCategory->items(),
                ]
            ];
        });
    }

    public function filterCategory($categoryId, BookFilteredByCategoryRequest $request, CategoryRepository $categoryRepository)
    {
        $category = $categoryRepository->find($categoryId);
        $officeId = $request->get('office_id');

        $input = $request->all();

        if (!$category) {
            throw new NotFoundException;
        }

        $relations = [
            'owners' => function ($q) {
                $q->select($this->ownerSelect);
            },
            'category' => function ($q) {
                $q->select($this->categorySelect);
            },
            'image' => function ($q) {
                $q->select($this->imageSelect);
            },
            'office' => function ($q) {
                $q->select($this->officeSelect);
            }
        ];

        return $this->getData(function () use ($relations, $category, $input, $officeId) {
            $bookCategory = $this->repository->getBookFilteredByCategory($category->id, $input, $this->select, $relations, $officeId);
            $currentPage = $bookCategory->currentPage();

            $this->compacts['items'] = [
                'total' => $bookCategory->total(),
                'per_page' => $bookCategory->perPage(),
                'current_page' => $currentPage,
                'next_page' => ($bookCategory->lastPage() > $currentPage) ? $currentPage + 1 : null,
                'prev_page' => $currentPage - 1 ?: null,
                'category' => [
                    'id' => $category->id,
                    'name_vi' => $category->name_vi,
                    'name_en' => $category->name_en,
                    'name_jp' => $category->name_jp,
                    'data' => $bookCategory->items(),
                ]
            ];
        });
    }

    public function addOwner($id, LogReputationRepository $logReputationRepository)
    {
        return $this->requestAction(function () use ($id, $logReputationRepository) {
            $book = $this->repository->addOwner($id, $logReputationRepository);
            $this->compacts['items'] = [
                'book' => $book,
            ];
        });
    }

    public function removeOwner($id)
    {
        return $this->doAction(function () use ($id) {
            $book = $this->repository->findOrFail($id);
            $this->before('delete', $book);

            $this->repository->removeOwner($book);
        }, __FUNCTION__);
    }

    public function uploadMedia(UploadMediaRequest $request, MediaRepository $mediaRepository)
    {
        $data = $request->all();

        return $this->doAction(function () use ($data, $mediaRepository) {
            $book = $this->repository->findOrFail($data['book_id']);
            $this->before('update', $book);

            $this->compacts['item'] = $this->repository->uploadMedia($book, $data, $mediaRepository);
        }, __FUNCTION__);
    }

    public function office($officeId, OfficeRepository $officeRepository)
    {
        $office = $officeRepository->find($officeId);

        if (!$office) {
            throw new NotFoundException;
        }

        $relations = [
            'owners' => function ($q) {
                $q->select($this->ownerSelect);
            },
            'category' => function ($q) {
                $q->select($this->categorySelect);
            },
            'image' => function ($q) {
                $q->select($this->imageSelect);
            },
            'category' => function ($q) {
                $q->select($this->categorySelect);
            }
        ];

        return $this->getData(function () use ($relations, $office) {
            $bookOffice = $this->repository->getBookByOffice($office->id, $this->select, $relations);
            $currentPage = $bookOffice->currentPage();

            $this->compacts['item'] = [
                'total' => $bookOffice->total(),
                'per_page' => $bookOffice->perPage(),
                'current_page' => $currentPage,
                'next_page' => ($bookOffice->lastPage() > $currentPage) ? $currentPage + 1 : null,
                'prev_page' => $currentPage - 1 ?: null,
                'office' => [
                    'id' => $office->id,
                    'name' => $office->name,
                    'data' => $bookOffice->items(),
                ]
            ];
        });
    }

    public function getTotalBook()
    {
        return $this->getData(function () {
            $this->compacts['item'] = $this->repository->countRecord();
        });
    }

    public function getBookList()
    {
        return $this->getData(function () {
            $data = $this->repository->getByPage();

            $this->compacts['items'] = $this->reFormatPaginate($data);
        });
    }

    public function countBook()
    {
        return $this->getData(function () {
            $this->compacts['item'] = $this->repository->countHaveBook();
        });
    }

    public function destroyBook($id)
    {
        try {
            return $this->doAction(function () use ($id) {
                $deleteBook = $this->repository->findOrFail($id);

                $this->repository->destroyBook($deleteBook);
            }, __FUNCTION__);
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }

    public function addBookOffice($book_id, LogReputationRepository $logReputationRepository)
    {
        try {
            $officeIdUserCurrent = $this->user->office_id;
            
            return $this->doAction(function () use ($book_id, $officeIdUserCurrent, $logReputationRepository) {
                $book = $this->repository->findOrFail($book_id);
                $this->compacts['items'] = $this->repository->storeBookOffice($book, $officeIdUserCurrent, $logReputationRepository);
            }, __FUNCTION__);
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }

    public function getListBookCurrentUser($book_name, $action = '')
    {
        try {
            return $this->getData(function () use ($book_name, $action) {
                $this->compacts['items'] = $this->repository->getListBookCurrentUser($book_name, $action);
            });
        } catch (ModelNotFoundException $e) {
            Log::error(new $e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }
    }
}
