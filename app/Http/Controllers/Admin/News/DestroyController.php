<?php

namespace App\Http\Controllers\Admin\News;

use App\Application\Core\News\Exceptions\NewsNotFoundException;
use App\Application\Core\News\UseCases\Commands\Delete\Command;
use App\Application\Core\News\UseCases\Commands\Delete\Handler;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DestroyController extends Controller
{
    /**
     * Удалить новость
     */
    public function __invoke(Handler $handler, string $newsId): RedirectResponse
    {
        Gate::authorize('news.delete', $newsId);

        try {
            $command = new Command((int)$newsId);
            $handler->handle($command);
        } catch (NewsNotFoundException) {
            throw new NotFoundHttpException('Новость не найдена');
        }

        return redirect()->route('admin.news.index')->with('success', 'Новость успешно удалена');
    }
}
