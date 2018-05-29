<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\ReviewRepository;
use App\Contracts\Repositories\CommentRepository;
use App\Contracts\Repositories\VoteRepository;
use App\Contracts\Repositories\BookRepository;
use App\Contracts\Repositories\MediaRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\LogReputationRepository;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Book\CommentRequest;
use App\Http\Requests\Api\Book\EditCommentRequest;
use Log;

class ReviewController extends ApiController
{
    const RE_VOTE_NUMBER = 2;

    public function __construct(ReviewRepository $repository)
    {
        parent::__construct($repository);
    }

    public function delete($reviewId)
    {
        return $this->doAction(function () use ($reviewId) {
            return $this->repository->delete($reviewId);
        }, __FUNCTION__);
    }

    public function reviewDetails(
        VoteRepository $voteRepository,
        BookRepository $bookRepository,
        MediaRepository $mediaRepository,
        $reviewId,
        $userId
    ) {
        return $this->doAction(function () use ($voteRepository, $bookRepository, $mediaRepository, $reviewId, $userId) {
            $review = $this->repository->reviewDetails($reviewId, $userId);
            $currentUser = $voteRepository->checkUserVoted($userId, $reviewId);
            $currentUser = $voteRepository->checkUserVoted($userId, $reviewId);
            $book = $bookRepository->show($review->book_id);
            $media = $mediaRepository->findImage($review->book_id);
            $userVoted = $currentUser ? $currentUser : null;
            $comments = [];
            foreach ($review->comments as $comment) {
                array_push($comments, [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => $comment->user,
                    'created_at' => $comment->time_ago
                ]);
            }

            $this->compacts['items'] = [
                'id' => $review->id,
                'user_id' => $review->user_id,
                'title' => $review->title,
                'content' => $review->content,
                'up_vote' => $review->up_vote,
                'down_vote' => $review->down_vote,
                'current_vote' => $userVoted,
                'book' => $book,
                'media' => $media,
                'comments' => $comments
            ];
        }, __FUNCTION__);
    }

    public function vote(
        Request $request,
        VoteRepository $voteRepository,
        UserRepository $userRepository,
        LogReputationRepository $logReputationRepository
    ) {
        return $this->doAction(function () use (
            $request,
            $voteRepository,
            $userRepository,
            $logReputationRepository
        ) {

            $check = $voteRepository->checkVoted($request->userId, $request->reviewId);
            if ($check) {
                $this->compacts['check'] = $check;
                if ($check->status == $request->status) {
                    $this->compacts['items'] = [
                        'messages' => config('model.review_messeges.can_not_vote')
                    ];
                } else {
                    $voteRepository->changeStatus($request->userId, $request->reviewId, $request->status);
                    if ($request->status == config('model.request_vote.up_vote')) {
                        $checkUpVoted = $voteRepository->checkUpVoted($request->userId, $request->reviewId);
                        if (!$checkUpVoted) {
                            $userRepository->addReputation($request->reviewerId, config('model.reputation.be_upvoted'), $check->id, config('model.log_type.be_upvoted'), $logReputationRepository);
                        }
                        $this->repository->increaseVote($request->reviewId, self::RE_VOTE_NUMBER);
                    } else {
                        $this->repository->decreaseVote($request->reviewId, self::RE_VOTE_NUMBER);
                    }
                    $this->compacts['items'] = ['messages' => config('model.review_messeges.revote_success')];
                }
            } else {
                $voteCheck = $voteRepository->addNewVote($request->userId, $request->reviewId, $request->status);
                $check = $voteRepository->checkVoted($request->userId, $request->reviewId);

                if (!$voteCheck) {
                    return $this->compacts['items'] = [
                        'messages' => config('model.review_messeges.owner_can_not_vote')
                    ];
                }
                if ($request->status == config('model.request_vote.up_vote')) {
                    $this->repository->increaseVote($request->reviewId);
                    $userRepository->addReputation($request->reviewerId, config('model.reputation.be_upvoted'), $check->id, config('model.log_type.be_upvoted'), $logReputationRepository);
                } else {
                    $this->repository->decreaseVote($request->reviewId);
                }
                $this->compacts['items'] = [
                    'messages' => config('model.review_messeges.vote_success')
                ];
            }

        }, __FUNCTION__);
    }

    public function commentReview(CommentRequest $request)
    {
        return $this->doAction(function () use ($request) {
            $this->repository->newComment($request->all());
        }, __FUNCTION__);
    }

    public function editCommentReview(CommentRepository $commentRepository, EditCommentRequest $request)
    {
        $dataComment = $request->only(
            'reviewId',
            'userId',
            'id',
            'content'
        );

        return $this->doAction(function () use ($commentRepository, $dataComment) {
            $commentRepository->updateComment($dataComment);
        }, __FUNCTION__);
        return $request->all();
    }

    public function removeComment(CommentRepository $commentRepository, $commentId)
    {
        return $this->doAction(function () use ($commentRepository, $commentId) {
            $commentRepository->removeComment($commentId);
        }, __FUNCTION__);
    }
}
