<?php

namespace App\Http\Controllers\Admin\Categories;

use App\Application\Core\Category\UseCases\Queries\SearchCategories\Fetcher;
use App\Application\Core\Category\UseCases\Queries\SearchCategories\Query;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class IndexController extends Controller
{
    /**
     * Показать список категорий
     */
    public function __invoke(Request $request, Fetcher $fetcher): View
    {
        Gate::authorize('category.viewAny');

        $query = Query::fromRequest($request->all());
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

        return view('admin.categories.index', compact('categories'));
    }
}
