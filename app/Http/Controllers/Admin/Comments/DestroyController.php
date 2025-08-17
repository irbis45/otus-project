<?php

namespace App\Http\Controllers\Admin\Comments;

use App\Application\Core\Comment\Exceptions\CommentNotFoundException;
use App\Application\Core\Comment\UseCases\Commands\Delete\Command;
use App\Application\Core\Comment\UseCases\Commands\Delete\Handler;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DestroyController extends Controller
{
    /**
     * Удалить категорию
     */
    public function __invoke(Handler $handler, string $commentId): RedirectResponse
    {
        Gate::authorize('comment.delete', $commentId);

        try {
            $command = new Command((int)$commentId);
            $handler->handle($command);

        } catch (CommentNotFoundException) {
            throw new NotFoundHttpException('Комментарий не найден');
        }

        return redirect()->route('admin.comments.index')
            ->with('success', 'Комментарий успешно удален');
    }
}
