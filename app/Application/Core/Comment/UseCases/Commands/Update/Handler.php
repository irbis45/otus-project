<?php

namespace App\Application\Core\Comment\UseCases\Commands\Update;

use App\Application\Core\Comment\DTO\CommentDTO;
use App\Application\Core\Comment\DTO\StatusDTO;
use App\Application\Core\Comment\Exceptions\CommentNotFoundException;
use App\Application\Core\Comment\Repositories\CommentRepositoryInterface;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Application\Core\Comment\UseCases\Commands\Update\Command;
use App\Application\Core\Comment\DTO\AuthorDTO;

class Handler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(Command $command): CommentDTO
    {
        $comment = $this->commentRepository->find($command->id);

        if (!$comment) {
            throw new CommentNotFoundException('Комментарий не найден');
        }

        $comment->{$comment->getColumnName('text')}  = $command->text;

        if (!is_null($command->status)) {
            $comment->{$comment->getColumnName('status')} = $command->status;
        }

        $this->commentRepository->save($comment);

        $author   = $comment->getAuthorId() ? $this->userRepository->find($comment->getAuthorId()) : null;

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
