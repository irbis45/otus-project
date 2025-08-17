<?php

namespace App\Http\Controllers\Admin\Categories;

use App\Application\Contracts\CacheInterface;
use App\Application\Core\Category\Exceptions\CategoryAlreadyExistsException;
use App\Application\Core\Category\Exceptions\CategoryNotFoundException;
use App\Application\Core\Category\UseCases\Commands\Update\Command;
use App\Application\Core\Category\UseCases\Commands\Update\Handler;
use App\Application\Core\Category\UseCases\Queries\FetchById\Fetcher;
use App\Application\Core\Category\UseCases\Queries\FetchById\Query;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Gate;

class UpdateController extends Controller
{

    public function __construct(
        private CacheInterface $cache,
    )
    {
    }

    /**
     * Показать форму редактирования категории
     */
    public function edit(Fetcher $fetcher, string $categoryId): View
    {
        Gate::authorize('category.update', $categoryId);

        try {
            $query = new Query((int)$categoryId);
            $category = $fetcher->fetch($query);
        } catch (CategoryNotFoundException) {
            throw new NotFoundHttpException('Категория не найдена');
        }

        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Обновить данные категории
     */
    public function update(UpdateCategoryRequest $request, Handler $handler, string $categoryId)
    {
        Gate::authorize('category.update', $categoryId);

        try {
            $command = new Command(
                id: (int)$categoryId,
                name: $request->get('name'),
                description: $request->get('description'),
                active: $request->has('active') ? true : false,
            );

            $category = $handler->handle($command);

            $this->cache->flushTagged('categories');

            return redirect()->route('admin.categories.index')
                             ->with('success', "Категория '{$category->name}' успешно обновлена");

        } catch (CategoryAlreadyExistsException $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', $e->getMessage());

        } catch (\Exception) {
            throw new NotFoundHttpException('Категория не найдена');
        }
    }
}
