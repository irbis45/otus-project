<?php

namespace App\Http\Controllers\Public;

use App\Application\Core\Category\UseCases\Queries\FetchAllPagination\Fetcher as CategoryFetcher;
use App\Application\Core\Category\UseCases\Queries\FetchAllPagination\Query as CategoryQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryPageRequest;
use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryController extends Controller
{
    private const CATEGORIES_PER_PAGE = 20;

    public function __invoke(CategoryPageRequest $request, CategoryFetcher $fetcher): View
    {
        $page = (int)$request->input('page', 1);

        $query = CategoryQuery::fromPage($page, self::CATEGORIES_PER_PAGE, true);
        $paginatedResult = $fetcher->fetch($query);

        $categories = new LengthAwarePaginator(
            items: $paginatedResult->items,
            total: $paginatedResult->total,
            perPage: $paginatedResult->getPerPage(),
            currentPage: $paginatedResult->getCurrentPage(),
            options: [
                       'path' => $request->url(),
                       'pageName' => 'page',
                   ]
        );

        $categories->withQueryString();

        return view('categories.index', compact('categories'));
    }
}
