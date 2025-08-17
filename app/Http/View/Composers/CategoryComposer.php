<?php

declare(strict_types=1);

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Application\Core\Category\UseCases\Queries\FetchPopular\Fetcher as PopularCategoriesFetcher;
use App\Application\Core\Category\UseCases\Queries\FetchPopular\Query as PopularCategoriesFetcherQuery;

class CategoryComposer
{

    public function __construct(private PopularCategoriesFetcher $popularCategoriesFetcher)
    {
    }

    public function compose(View $view)
    {
        $popularCategories = $this->popularCategoriesFetcher->fetch(new PopularCategoriesFetcherQuery())->results;

        $view->with('popularCategories', $popularCategories);
    }
}
