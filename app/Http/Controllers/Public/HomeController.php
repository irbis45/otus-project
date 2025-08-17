<?php

namespace App\Http\Controllers\Public;

use App\Application\Core\News\UseCases\Queries\FetchAllPagination\Fetcher as LatestFetcher;
use App\Application\Core\News\UseCases\Queries\FetchAllPagination\Query as LatestQuery;
use App\Application\Core\News\UseCases\Queries\FetchFeatured\Fetcher as FeaturedFetcher;
use App\Application\Core\News\UseCases\Queries\FetchFeatured\Query as FeaturedQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\HomePageRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

//use Illuminate\Http\Request;

class HomeController extends Controller
{
    private const NEWS_PER_PAGE = 5;
    private const FEATURED_NEWS_PER_PAGE = 3;

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function __invoke(HomePageRequest $request, LatestFetcher $latestFetcher, FeaturedFetcher $featuredFetcher): View
    {
        //dd(auth()->user()->hasRole(Role::EDITOR));
        //dd(auth()->user()->roles);
       // dd(auth()->user()->permissions());
        //dd(auth()->user()->hasPermission(Permission::CREATE_NEWS));

        $page = (int)$request->input('page', 1);

        $query = LatestQuery::fromPage($page, self::NEWS_PER_PAGE, true);
        $paginatedResult = $latestFetcher->fetch($query);

        // Преобразуем PaginatedResult в LengthAwarePaginator для шаблона
        $latestNews = new LengthAwarePaginator(
            items: $paginatedResult->items,
            total: $paginatedResult->total,
            perPage: $paginatedResult->getPerPage(),
            currentPage: $paginatedResult->getCurrentPage(),
            options: [
                       'path' => $request->url(),
                       'pageName' => 'page',
                   ]
        );

        $latestNews->withQueryString();

        $featuredNews = $featuredFetcher->fetch(new FeaturedQuery(self::FEATURED_NEWS_PER_PAGE))->results;

        return view('home', compact('latestNews', 'featuredNews'));
    }
}
