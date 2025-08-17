<?php

namespace App\Application\Core\Comment\UseCases\Queries\FetchById;

use App\Application\Core\Comment\DTO\AuthorDTO;
use App\Application\Core\Comment\DTO\CommentDTO;
use App\Application\Core\Comment\DTO\StatusDTO;
use App\Application\Core\Comment\Exceptions\CommentNotFoundException;
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

    /**
     * @param \App\Application\Core\Comment\UseCases\Queries\FetchById\Query $query
     *
     * @return CommentDTO
     * @throws CommentNotFoundException
     */
    public function fetch(Query $query): CommentDTO
    {
        /** @var ?Comment $comment */
        $comment = $this->commentRepository->find($query->id);

        if (!$comment) {
            throw new CommentNotFoundException('Комментарий не найден');
        }

        $author = $comment->getAuthorId() ? $this->userRepository->find($comment->getAuthorId()) : null;
        $statusEnum = $comment->getStatus();

        return new CommentDTO(
            id:                 $comment->getId(),
            text:               $comment->getText(),
            author:            $author ? new AuthorDTO(
                                    id: $author->getId(),
                                    name: $author->getName(),
                                    email: $author->getEmail(),
                                ) : null,
            newsId:             $comment->getNewsId(),
            status:             new StatusDTO(
                                    value: $statusEnum->value,
                                    label: $statusEnum->label(),
                                ),
            parentId:           $comment->getParentId(),
            createdAt:          $comment->getCreatedAt() ? new \DateTimeImmutable($comment->getCreatedAt()) : null,
            updatedAt:          $comment->getUpdatedAt() ? new \DateTimeImmutable($comment->getUpdatedAt()) : null,
        );
    }
}
