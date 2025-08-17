<?php

namespace App\Http\Controllers\Admin\News;

use App\Application\Core\News\Exceptions\NewsNotFoundException;
use App\Application\Core\News\UseCases\Queries\FetchById\Fetcher;
use App\Application\Core\News\UseCases\Queries\FetchById\Query;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Gate;

class ShowController extends Controller
{
    public function __invoke(Fetcher $fetcher, string $newsId): View
    {
        Gate::authorize('news.view', $newsId);

        try {
            $query = new Query((int)$newsId);
            $news = $fetcher->fetch($query);

        } catch (NewsNotFoundException) {
            throw new NotFoundHttpException('Новость не найдена');
        }

        return view('admin.news.show', compact('news'));
    }
}
