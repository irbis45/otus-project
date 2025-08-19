<?php

namespace App\Http\Controllers\Admin\Comments;

use App\Application\Core\Comment\DTO\StatusDTO;
use App\Application\Core\Comment\Enums\CommentStatus;
use App\Application\Core\Comment\UseCases\Queries\SearchComments\Fetcher;
use App\Application\Core\Comment\UseCases\Queries\SearchComments\Query;
use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class IndexController extends Controller
{
    /**
     * Показать список комментариев
     */
    public function __invoke(Request $request, Fetcher $fetcher, NewsRepositoryInterface $newsRepository): View
    {
        Gate::authorize('comment.viewAny');

        $page = max(1, (int) $request->get('page', 1));
        $perPage = 10;
        $search = $request->get('search');
        $newsId = $request->get('news_id') ? (int) $request->get('news_id') : null;
        $status = $request->get('status');

        $query = Query::fromPage($page, $perPage, $search, $newsId, $status);
        $paginatedResult = $fetcher->fetch($query);

        $comments = new LengthAwarePaginator(
            items: $paginatedResult->items,
            total: $paginatedResult->total,
            perPage: $paginatedResult->getPerPage(),
            currentPage: $paginatedResult->getCurrentPage(),
            options: [
                       'path' => $request->url(),
                       'pageName' => 'page',
                   ]
        );

        $comments->withQueryString();

        $statuses = array_map(fn(CommentStatus $case) => new StatusDTO($case->value, $case->label()), CommentStatus::cases());
        
        // Получаем только выбранную новость для отображения в фильтре
        $selectedNews = null;
        if ($newsId) {
            $selectedNews = $newsRepository->find($newsId);
        }

        return view('admin.comments.index', compact('comments', 'statuses', 'selectedNews'));
    }
}
