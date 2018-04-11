<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\BookRepository;
use App\Http\Requests\Api\HomeFilterRequest;
use App\Contracts\Repositories\OwnerRepository;
use Illuminate\Http\Request;

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
        'name',
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

    protected $bookRepository;
    protected $ownerRepository;

    public function __construct(BookRepository $bookRepository, OwnerRepository $ownerRepository)
    {
        parent::__construct();
        $this->bookRepository = $bookRepository;
        $this->ownerRepository = $ownerRepository;
    }

    public function index(Request $request)
    {
        $officeId = $request->get('office_id');
        $top = $this->getData(function() {
            $this->compacts['item'] = $this->ownerRepository->topOwnBook();
        });

        $relations = [
            'image' => function ($q) {
                $q->select($this->imageSelect);
            },
            'owners'=> function ($q) {
                $q->select($this->ownerSelect);
            },
            'office' => function ($q) {
                $q->select($this->officeSelect);
            },
        ];

        return $this->getData(function() use ($relations, $officeId, $top){
            $this->compacts['items'] = $this->bookRepository->getDataInHomepage($relations, $this->bookSelect, $officeId, $top);
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

        return $this->getData(function() use ($relations, $filters, $officeId){
            $this->compacts['items'] = $this->bookRepository->getDataFilterInHomepage(
                $relations, $this->bookSelect, $filters, $officeId
            );
        });
    }
}
