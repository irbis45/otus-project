<?php

namespace App\Http\Controllers\Admin\Comments;

use App\Application\Core\Comment\DTO\StatusDTO;
use App\Application\Core\Comment\Enums\CommentStatus;
use App\Application\Core\Comment\Exceptions\CommentNotFoundException;
use App\Application\Core\Comment\UseCases\Commands\Update\Command;
use App\Application\Core\Comment\UseCases\Commands\Update\Handler;
use App\Application\Core\Comment\UseCases\Queries\FetchById\Fetcher;
use App\Application\Core\Comment\UseCases\Queries\FetchById\Query;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCommentRequest;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Gate;

class UpdateController extends Controller
{

    /**
     * Показать форму редактирования категории
     */
    public function edit(Fetcher $fetcher, string $commentId): View
    {
        Gate::authorize('comment.update', $commentId);

        try {
            $query = new Query((int)$commentId);
            $comment = $fetcher->fetch($query);

            $statuses = array_map(fn(CommentStatus $case) => new StatusDTO($case->value, $case->label()), CommentStatus::cases());

        } catch (CommentNotFoundException) {
            throw new NotFoundHttpException('Комментарий не найдена');
        }

        return view('admin.comments.edit', compact('comment', 'statuses'));
    }

    /**
     * Обновить данные категории
     */
    public function update(UpdateCommentRequest $request, Handler $handler, string $commentId)
    {
        Gate::authorize('comment.update', $commentId);

        try {
            $command = new Command(
                id: (int)$commentId,
                text: $request->get('text'),
                status: CommentStatus::from($request->get('status')),
            );

            $comment = $handler->handle($command);

            return redirect()->route('admin.comments.index')
                             ->with('success', "Комментарий '{$comment->id}' успешно обновлена");

        } catch (\Exception) {
            throw new NotFoundHttpException('Комментарий не найдена');
        }
    }
}
