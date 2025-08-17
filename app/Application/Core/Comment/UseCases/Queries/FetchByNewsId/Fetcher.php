<?php

namespace App\Application\Core\Comment\UseCases\Queries\FetchByNewsId;

use App\Application\Core\Comment\DTO\AuthorDTO;
use App\Application\Core\Comment\DTO\CommentDTO;
use App\Application\Core\Comment\DTO\ResultDTO;

use App\Application\Core\Comment\DTO\StatusDTO;
use App\Application\Core\Comment\Repositories\CommentRepositoryInterface;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Models\Comment;

class Fetcher
{
    /**
     * @param CommentRepositoryInterface $commentRepository
     */
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function fetch(Query $query): ResultDTO
    {
        /** @var ?Comment $comment */
        $comments = $this->commentRepository->fetchAllByNewsId($query->newsId);

        $authorIds = array_unique(array_map(fn(Comment $c) => $c->getAuthorId(), $comments));
        $authors = $this->userRepository->findByIds($authorIds);

        $commentsById = [];
        foreach ($comments as $comment) {
            $comment->setReplies([]);
            $commentsById[$comment->getId()] = $comment;
        }

        $rootComments = [];
        foreach ($comments as $comment) {
            $parentId = $comment->getParentId();
            if ($parentId === null) {
                $rootComments[] = $comment;
            } elseif (isset($commentsById[$parentId])) {
                $commentsById[$parentId]->setReplies(
                    array_merge($commentsById[$parentId]->getReplies() ?? [], [$comment])
                );
            }
        }

        $commentDTOs = array_map(fn(Comment $comment) => $this->toCommentDTO($comment, $authors), $rootComments);

        return new ResultDTO($commentDTOs);
    }

    /**
     * Преобразует Comment в CommentDTO
     *
     * @param Comment $comment
     * @param array<int, \App\Models\User> $authors
     */
    private function toCommentDTO(Comment $comment, array $authors): CommentDTO
    {
        $authorModel = $authors[$comment->getAuthorId()] ?? null;

        $authorDTO = $authorModel ? new AuthorDTO(
            id: $authorModel->getId(),
            name: $authorModel->getName(),
            email: $authorModel->getEmail(),
        ) : null;

        $statusEnum = $comment->getStatus();

        return new CommentDTO(
            id: $comment->getId(),
            text: $comment->getText(),
            author: $authorDTO,
            newsId: $comment->getNewsId(),
            status: new StatusDTO(
                    value: $statusEnum->value,
                    label: $statusEnum->label(),
                ),
            parentId: $comment->getParentId(),
            createdAt: $comment->getCreatedAt() ? new \DateTimeImmutable($comment->getCreatedAt()) : null,
            updatedAt: $comment->getUpdatedAt() ? new \DateTimeImmutable($comment->getUpdatedAt()) : null,
            replies: array_map(fn(Comment $reply) => $this->toCommentDTO($reply, $authors), $comment->getReplies() ?? [])
        );
    }
}
