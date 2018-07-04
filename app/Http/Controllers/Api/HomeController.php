<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\BookRepository;
use App\Http\Requests\Api\HomeFilterRequest;
use App\Contracts\Repositories\OwnerRepository;
use Illuminate\Http\Request;
use App\Contracts\Repositories\PostRepository;

class HomeController extends ApiController
{
    protected $bookSelect = [
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

    protected $ownerSelect = [
        'id',
        'name',
        'avatar',
        'position',
    ];

    protected $postSelect = [
        'id',
        'slide_url',
        'title',
        'slug',
        'content',
    ];

    protected $bookRepository;
    protected $ownerRepository;
    protected $postRepository;

    public function __construct(BookRepository $bookRepository, OwnerRepository $ownerRepository, PostRepository $postRepository)
    {
        parent::__construct();
        $this->bookRepository = $bookRepository;
        $this->ownerRepository = $ownerRepository;
        $this->postRepository = $postRepository;
    }

    public function index(Request $request)
    {
        $officeId = $request->get('office_id');
        $top = $this->getData(function () {
            $this->compacts['item'] = $this->ownerRepository->topOwnBook();
        });

        $relations = [
            'image' => function ($q) {
                $q->select($this->imageSelect);
            },
            'category' => function ($q) {
                $q->select($this->categorySelect);
            },
            'owners'=> function ($q) {
                $q->select($this->ownerSelect);
            },
            'office' => function ($q) {
                $q->select($this->officeSelect);
            },
        ];

        return $this->getData(function () use ($relations, $officeId, $top) {
            $this->compacts['items'] = $this->bookRepository->getDataInHomepage($relations, $this->bookSelect, $officeId, $top);
            $this->compacts['posts'] = $this->postRepository->getDataPostHome();
        });
    }

    public function filter(HomeFilterRequest $request)
    {
        $filters = $request->get('filters') ?: [];
        $officeId = $request->get('office_id');
        $relations = [
            'image' => function ($q) {
                $q->select($this->imageSelect);
            },
            'category' => function ($q) {
                $q->select($this->categorySelect);
            },
            'office' => function ($q) {
                $q->select($this->officeSelect);
            },
            'owners'=> function ($q) {
                $q->select($this->ownerSelect);
            },
        ];

        return $this->getData(function () use ($relations, $filters, $officeId) {
            $this->compacts['items'] = $this->bookRepository->getDataFilterInHomepage(
                $relations,
                $this->bookSelect,
                $filters,
                $officeId
            );
        });
    }

    public function getListPost()
    {
        return $this->getData(function () {
            $this->compacts['items'] = $this->postRepository->getData();
        });
    }

    public function getPost($id)
    {
        return $this->getData(function () use ($id) {
            $this->compacts['items'] = $this->postRepository->getDataPost($id);
            $this->compacts['suggest'] = $this->postRepository->getDataPostHome();
        });
    }
}
