<?php

namespace App\Http\Controllers\Admin\Comments;

use App\Application\Core\Comment\Exceptions\CommentNotFoundException;
use App\Application\Core\Comment\UseCases\Queries\FetchById\Fetcher;
use App\Application\Core\Comment\UseCases\Queries\FetchById\Query;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Gate;

class ShowController extends Controller
{
    /**
     * Показать детали пользователя
     */
    public function __invoke(Fetcher $fetcher, string $commentId): View
    {
        Gate::authorize('comment.view', $commentId);

        try {
            $query = new Query((int)$commentId);
            $comment = $fetcher->fetch($query);
        } catch (CommentNotFoundException) {
            throw new NotFoundHttpException('Комментарий не найден');
        }

        return view('admin.comments.show', compact('comment'));
    }
}
