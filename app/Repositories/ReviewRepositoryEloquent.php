<?php

namespace App\Repositories;

use App\Contracts\Repositories\ReviewRepository;
use App\Eloquent\Review;
use App\Eloquent\Notification;
use Illuminate\Support\Facades\Event;
use App\Events\NotificationHandler;
use Auth;

class ReviewRepositoryEloquent extends AbstractRepositoryEloquent implements ReviewRepository
{
    public function model()
    {
        return new Review;
    }

    public function delete($reviewId)
    {
        return $this->model()->destroy($reviewId);
    }

    public function reviewDetails($reviewId, $userId)
    {
        return $this->model()->findOrFail($reviewId);
    }

    public function vote($userId, $reviewId, $status)
    {

        $check = $this->model()->where([
            ['user_id', '=', $userId],
            ['review_id', '=', $reviewId],
        ])->first();

        return $check;
    }

    public function increaseVote($reviewId, $voteNumber = 1)
    {
        $review = Review::findOrFail($reviewId);
        Event::fire('androidNotification', config('model.notification.up_vote'));
        $message = trans('review.upvoted') . $this->user->name;
        event(new NotificationHandler($message, $review->user_id, config('model.notification.up_vote')));
        Event::fire('notification', [
            [
                'current_user_id' => $this->user->id,
                'get_user_id' => $review->user_id,
                'target_id' => $review->book->id,
                'type' => config('model.notification.up_vote'),
            ]
        ]);

        return $this->model()->where('id', $reviewId)->increment('up_vote', $voteNumber);
    }

    public function decreaseVote($reviewId, $voteNumber = 1)
    {
        $review = Review::findOrFail($reviewId);
        Event::fire('androidNotification', config('model.notification.down_vote'));
        $message = trans('review.downvoted') . $this->user->name;
        event(new NotificationHandler($message, $review->user_id, config('model.notification.down_vote')));
        Event::fire('notification', [
            [
                'current_user_id' => $this->user->id,
                'get_user_id' => $review->user_id,
                'target_id' => $review->book->id,
                'type' => config('model.notification.down_vote'),
            ]
        ]);

        return $this->model()->where('id', $reviewId)->increment('down_vote', $voteNumber);
    }

    public function newComment($data)
    {
        return $this->model()->findOrFail($data['item']['reviewId'])->comments()->create(
            [
                'user_id' => $data['item']['userId'],
                'review_id' => $data['item']['reviewId'],
                'content' => $data['item']['content'],
            ]
        );
    }
}
