<?php

namespace App\Application\Core\Comment\UseCases\Commands\Delete;

use App\Application\Core\Comment\Exceptions\CommentNotFoundException;
use App\Application\Core\Comment\Repositories\CommentRepositoryInterface;

class Handler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository
    ) {
    }

    public function handle(Command $command): bool
    {
        $user = $this->commentRepository->find($command->id);

        if (!$user) {
            throw new CommentNotFoundException('Комментарий не найден');
        }

        return $this->commentRepository->delete($user);
    }
}
