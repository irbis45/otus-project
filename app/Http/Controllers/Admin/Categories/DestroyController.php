<?php

namespace App\Http\Controllers\Admin\Categories;

use App\Application\Contracts\CacheInterface;
use App\Application\Core\Category\Exceptions\CategoryNotFoundException;
use App\Application\Core\Category\UseCases\Commands\Delete\Command;
use App\Application\Core\Category\UseCases\Commands\Delete\Handler;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Gate;

class DestroyController extends Controller
{
    public function __construct(
        private CacheInterface $cache,
    )
    {
    }

    /**
     * Удалить категорию
     */
    public function __invoke(Handler $handler, string $categoryId): RedirectResponse
    {
        Gate::authorize('category.delete', $categoryId);

        try {
            $command = new Command((int)$categoryId);
            $handler->handle($command);

            $this->cache->flushTagged('categories');
        } catch (CategoryNotFoundException) {
            throw new NotFoundHttpException('Категория не найдена');
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Категория успешно удалена');
    }
}
