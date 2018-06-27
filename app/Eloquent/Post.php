<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slide_url',
        'title',
        'slug',
        'content',
        'created_at',
        'updated_at'
    ];

    protected $dates = ['deleted_at'];

    protected $appends = ['web'];

    public function getWebAttribute()
    {
        if ($this->slide_url) {
            return $this->responseMediaStorage([
                'thumbnail_path' => 'thumbnail_web',
                'small_path' => 'small_web',
                'medium_path' => 'medium_web',
                'large_path' => 'large_web',
            ]);
        }

        return $this->thumb_path;
    }

    private function responseMediaStorage($size = null)
    {
        if (is_array($size)) {
            $mediaPath = [];

            foreach ($size as $item => $value) {
                $mediaPath[$item] = route(
                    'image',
                    ['path' => app()['glide.builder']->getUrl($this->slide_url, ['p' => ($value) ?: null])]
                );
            }

            return $mediaPath;
        }

        return route(
            'image',
            ['path' => app()['glide.builder']->getUrl($this->slide_url, ['p' => ($size) ?: null])]
        );
    }
}
