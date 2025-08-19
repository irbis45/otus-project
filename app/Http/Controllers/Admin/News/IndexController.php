<?php

namespace App\Http\Controllers\Admin\News;

use App\Application\Core\News\UseCases\Queries\SearchNews\Fetcher;
use App\Application\Core\News\UseCases\Queries\SearchNews\Query;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;


class IndexController extends Controller
{
    /**
     * Показать список новостей
     */
    public function __invoke(Request $request, Fetcher $fetcher): View
    {
        Gate::authorize('news.viewAny');

        $query = Query::fromRequest($request->all());
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

        $news->withQueryString();

        return view('admin.news.index', compact('news'));
    }
}
