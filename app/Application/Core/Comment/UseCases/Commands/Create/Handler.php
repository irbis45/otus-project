<?php

namespace App\Application\Core\Comment\UseCases\Commands\Create;

use App\Application\Core\Comment\DTO\AuthorDTO;
use App\Application\Core\Comment\DTO\CommentDTO;
use App\Application\Core\Comment\DTO\StatusDTO;
use App\Application\Core\Comment\Enums\CommentStatus;
use App\Application\Core\Comment\Exceptions\CommentSaveException;
use App\Application\Core\Comment\Repositories\CommentRepositoryInterface;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Models\Comment;
use DateTimeImmutable;

class Handler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(Command $command): CommentDTO
    {
        $comment = new Comment();

        $comment->{$comment->getColumnName('text')} = $command->text;
        $comment->{$comment->getColumnName('author_id')} = $command->authorId;
        $comment->{$comment->getColumnName('news_id')} = $command->newsId;
        $comment->{$comment->getColumnName('parent_id')} = $command->parentId;
        $comment->{$comment->getColumnName('status')} = CommentStatus::Pending->value;

        $result = $this->commentRepository->save($comment);

        if (!$result) {
            throw new CommentSaveException();
        }

        $author = $comment->getAuthorId() ? $this->userRepository->find($comment->getAuthorId()) : null;
        $statusEnum = $comment->getStatus();

        return new CommentDTO(
            id: $comment->getId(),
            text: $comment->getText(),
            author:            $author ? new AuthorDTO(
                    id: $author->getId(),
                    name: $author->getName(),
                    email: $author->getEmail(),
                ) : null,
            newsId:             $comment->getNewsId(),
            status: new StatusDTO(
                value: $statusEnum->value,
                label: $statusEnum->label(),
            ),
            parentId: $comment->getParentId(),
            createdAt: $comment->getCreatedAt() ? new DateTimeImmutable($comment->getCreatedAt()->toDateTimeString()) : null,
            updatedAt: $comment->getUpdatedAt() ? new DateTimeImmutable($comment->getUpdatedAt()->toDateTimeString()) : null,
            replies: [],
        );
    }
}
