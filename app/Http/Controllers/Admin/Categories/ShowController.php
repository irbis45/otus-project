<?php

namespace App\Http\Controllers\Admin\Categories;

use App\Application\Core\Category\Exceptions\CategoryNotFoundException;
use App\Application\Core\Category\UseCases\Queries\FetchById\Fetcher;
use App\Application\Core\Category\UseCases\Queries\FetchById\Query;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Gate;

class ShowController extends Controller
{
    /**
     * Показать детали категории
     */
    public function __invoke(Fetcher $fetcher, string $categoryId): View
    {
        Gate::authorize('category.view', $categoryId);

        try {
            $query = new Query((int)$categoryId);
            $category = $fetcher->fetch($query);
        } catch (CategoryNotFoundException) {
            throw new NotFoundHttpException('Категория не найдена');
        }

        return view('admin.categories.show', compact('category'));
    }
}
