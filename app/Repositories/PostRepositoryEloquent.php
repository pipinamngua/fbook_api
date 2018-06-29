<?php

namespace App\Repositories;

use App\Eloquent\Post;
use App\Contracts\Repositories\PostRepository;
use App\Exceptions\Api\ActionException;
use App\Exceptions\Api\NotFoundException;
use App\Exceptions\Api\UnknownException;
use App\Traits\Repositories\UploadableTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Log;
use Illuminate\Pagination\Paginator;

class PostRepositoryEloquent extends AbstractRepositoryEloquent implements PostRepository
{
    use UploadableTrait;

    public function model()
    {
        return new Post;
    }

    public function getDataPostHome($dataSelect = ['*'])
    {
        $posts = $this->model()
                ->select($dataSelect)
                ->orderBy('id', 'DESC')
                ->limit(config('model.post.limit'))
                ->get();

        return $posts;
    }

    public function getData($data = [], $with = [], $dataSelect = ['*'])
    {
        $posts = $this->model()
            ->select($dataSelect)
            ->with($with)
            ->orderBy('created_at', 'ASC')
            ->paginate(config('paginate.default'));

        return $posts;
    }

    public function getDataPost($id)
    {
        try {
            $post = $this->model()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }

        return $post;
    }

    public function getDataPostByTitle(array $data, $limit = '')
    {
        Paginator::currentPageResolver(function () use ($data) {
            return $data['page'];
        });

        return $this->model()
            ->where('title', 'like', '%' . $data['key'] . '%')
            ->latest()
            ->paginate($limit ?: config('paginate.default'));
    }

    public function store(array $data)
    {
        $slug = $this->createUrlSlug($data['title']);
        if (isset($data['medias'])) {
            $path = $this->uploadFile($data['medias'][0]['file'], strtolower(class_basename($this->model())), 'image');
        }
        
        return $this->model()->create([
                    'slide_url' => $path,
                    'title' => $data['title'],
                    'slug' => $slug,
                    'content' => $data['content'],
                    'created_at' => date('Y-m-d H:i:s'),
                    ]);
    }
    
    public function edit($id)
    {
        try {
            $post = $this->model()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());

            throw new NotFoundException();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw new UnknownException($e->getMessage(), $e->getCode());
        }

        return $post;
    }

    public function update(Post $post, array $data)
    {
        $slug = $this->createUrlSlug($data['title']);
        $path = $post->slide_url;
        if (isset($data['medias'])) {
            $path = $this->uploadFile($data['medias'][0]['file'], strtolower(class_basename($this->model())), 'image');
        }

        $post->update([
            'slide_url' => $path,
            'title' => $data['title'],
            'slug' => $slug,
            'content' => $data['content'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function destroy(Post $post)
    {
        $post->delete();
    }

    private function createUrlSlug($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
