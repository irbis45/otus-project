<?php

namespace App\Http\Controllers\Public;

use App\Application\Core\News\Exceptions\NewsNotFoundException;
use App\Application\Core\Category\Exceptions\CategoryNotFoundException;
use App\Application\Core\News\UseCases\Queries\FetchBySlug\Fetcher as NewsFetcher;
use App\Application\Core\News\UseCases\Queries\FetchBySlug\Query as NewsQuery;
use App\Application\Core\Comment\UseCases\Queries\FetchByNewsId\Fetcher as CommentFetcher;
use App\Application\Core\Comment\UseCases\Queries\FetchByNewsId\Query as CommentQuery;
use App\Application\Core\News\UseCases\Queries\FetchSearchPagination\Fetcher as NewsSearchFetcher;
use App\Application\Core\News\UseCases\Queries\FetchSearchPagination\Query as NewsSearchQuery;
use App\Application\Core\News\UseCases\Queries\FetchByCategoryPagination\Fetcher as NewsByCategoryFetcher;
use App\Application\Core\News\UseCases\Queries\FetchByCategoryPagination\Query as NewsByCategoryQuery;
use App\Application\Core\Category\UseCases\Queries\FetchBySlug\Fetcher as CategoryFetcher;
use App\Application\Core\Category\UseCases\Queries\FetchBySlug\Query as CategoryQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchNewsRequest;
use App\Http\Requests\NewsPageRequest;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Bus\Dispatcher;
use App\Application\Core\News\UseCases\Commands\TrackView\Command as TrackViewCommand;

class NewsController extends Controller
{
    public function __construct(private Dispatcher $dispatcher) {}
    private const NEWS_PER_PAGE = 10;
    private const SEARCH_NEWS_PER_PAGE = 2;

    public function show(NewsFetcher $newsFetcher, CommentFetcher $commentFetcher, string $newsSlug): View
    {
        try {
            $query = new NewsQuery($newsSlug);
            $news = $newsFetcher->fetch($query);

            $commentQuery = new CommentQuery($news->id);
            $comments = $commentFetcher->fetch($commentQuery)->results;

            $this->dispatcher->dispatch(new TrackViewCommand($news->id));

        } catch (NewsNotFoundException) {
            throw new NotFoundHttpException('Новость не найдена');
        }

        return view('news.show', compact('news', 'comments'));
    }


    public function byCategory(NewsPageRequest $request, CategoryFetcher $categoryFetcher, NewsByCategoryFetcher $newsFetcher, string $categorySlug): View
    {
        try {
            $categoryQuery = new CategoryQuery($categorySlug);
            $category = $categoryFetcher->fetch($categoryQuery);

            $page = (int)$request->input('page', 1);

            $query = NewsByCategoryQuery::fromPage($category->id, $page, self::NEWS_PER_PAGE);
            $paginatedResult = $newsFetcher->fetch($query);

            $news = new LengthAwarePaginator(
                items: $paginatedResult->items,
                total: $paginatedResult->total,
                perPage: $paginatedResult->getPerPage(),
                currentPage: $paginatedResult->getCurrentPage(),
                options: [
                           'path' => $request->url(),
                           'pageName' => 'page',
                       ]
            );

            $news->withQueryString();
        } catch (CategoryNotFoundException) {
            throw new NotFoundHttpException('Категория не найдена');
        }

        return view('news.by_category', compact('category', 'news'));
    }


    public function search(SearchNewsRequest $request, NewsSearchFetcher $fetcher): View {

        $page = (int)$request->input('page', 1);

        $searchQuery = $request->input('query', null);

        $query = NewsSearchQuery::fromPage($searchQuery, $page, self::SEARCH_NEWS_PER_PAGE);
        $paginatedResult = $fetcher->fetch($query);

        $news = new LengthAwarePaginator(
            items: $paginatedResult->items,
            total: $paginatedResult->total,
            perPage: $paginatedResult->getPerPage(),
            currentPage: $paginatedResult->getCurrentPage(),
            options: [
                       'path' => $request->url(),
                       'pageName' => 'page',
                   ]
        );

        // Сохраняем другие параметры для добавления к ссылкам пагинации
        if ($searchQuery !== null) {
            $news->appends(['query' => $searchQuery]);
        } else {
            // или, чтобы добавить все параметры запроса
            $news->withQueryString();
        }

        return view('news.search', compact('news', 'searchQuery'));
    }
}
