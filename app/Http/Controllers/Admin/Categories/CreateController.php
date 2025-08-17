<?php

namespace App\Http\Controllers\Admin\Categories;

use App\Application\Contracts\CacheInterface;
use App\Application\Core\Category\Exceptions\CategoryAlreadyExistsException;
use App\Application\Core\Category\Exceptions\CategorySaveException;
use App\Application\Core\Category\UseCases\Commands\Create\Command;
use App\Application\Core\Category\UseCases\Commands\Create\Handler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCategoryRequest;
use Exception;
use Illuminate\Support\Facades\Gate;

class CreateController extends Controller
{

    public function __construct(
        private CacheInterface $cache,
    )
    {
    }

    /**
     * Показать форму для создания категории
     */
    public function create()
    {
        Gate::authorize('category.create');

        return view('admin.categories.create');
    }

    /**
     * Сохранить новую категорию
     */
    public function store(CreateCategoryRequest $request, Handler $handler)
    {
        Gate::authorize('category.create');

        try {
            $command = new Command(
                name:        $request->get('name'),
                description: $request->get('description'),
                active:      $request->has('active') ? true : false,
            );

            $handler->handle($command);

            $this->cache->flushTagged('categories');

            return redirect()->route('admin.categories.index')->with('success', "Категория успешно создана");
        } catch (CategoryAlreadyExistsException $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', $e->getMessage());

        } catch (CategorySaveException $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', $e->getMessage());

        }  catch (Exception $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Произошла непредвиденная ошибка при создании категории. Попробуйте позже.');
        }
    }
}
