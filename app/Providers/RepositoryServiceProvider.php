<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class RepositoryServiceProvider extends ServiceProvider
{
    protected static $repositories = [
        'user' => [
            \App\Contracts\Repositories\UserRepository::class,
            \App\Repositories\UserRepositoryEloquent::class,
        ],
        'book' => [
            \App\Contracts\Repositories\BookRepository::class,
            \App\Repositories\BookRepositoryEloquent::class,
        ],
        'category' => [
            \App\Contracts\Repositories\CategoryRepository::class,
            \App\Repositories\CategoryRepositoryEloquent::class,
        ],
        'office' => [
            \App\Contracts\Repositories\OfficeRepository::class,
            \App\Repositories\OfficeRepositoryEloquent::class,
        ],
        'media' => [
            \App\Contracts\Repositories\MediaRepository::class,
            \App\Repositories\MediaRepositoryEloquent::class,
        ],
        'review' => [
            \App\Contracts\Repositories\ReviewRepository::class,
            \App\Repositories\ReviewRepositoryEloquent::class,
        ],
         'vote' => [
            \App\Contracts\Repositories\VoteRepository::class,
            \App\Repositories\VoteRepositoryEloquent::class,
         ],
        'comment' => [
            \App\Contracts\Repositories\CommentRepository::class,
            \App\Repositories\CommentRepositoryEloquent::class,
        ],
        'owner' => [
            \App\Contracts\Repositories\OwnerRepository::class,
            \App\Repositories\OwnerRepositoryEloquent::class,
        ],
        'log' => [
            \App\Contracts\Repositories\LogReputationRepository::class,
            \App\Repositories\LogReputationRepositoryEloquent::class,
        ],
        'post' => [
            \App\Contracts\Repositories\PostRepository::class,
            \App\Repositories\PostRepositoryEloquent::class,
        ],

    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            config('model.log_type.be_upvoted') => Vote::class,
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        foreach (static::$repositories as $repository) {
            $this->app->singleton(
                $repository[0],
                $repository[1]
            );
        }
    }
}
