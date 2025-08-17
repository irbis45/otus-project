<?php

namespace App\Http\Controllers\Admin\Users;

use App\Application\Core\Role\UseCases\Commands\ChangeUserRole\Command as ChangeRoleCommand;
use App\Application\Core\Role\UseCases\Commands\ChangeUserRole\Handler as ChangeRoleHandler;
use App\Application\Core\User\Exceptions\UserEmailAlreadyExistsException;
use App\Application\Core\User\Exceptions\UserNotFoundException;
use App\Application\Core\User\Exceptions\UserSaveException;
use App\Application\Core\User\UseCases\Commands\Update\Command;
use App\Application\Core\User\UseCases\Commands\Update\Handler;
use App\Application\Core\User\UseCases\Queries\FetchyById\Fetcher;
use App\Application\Core\User\UseCases\Queries\FetchyById\Query;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use Exception;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Application\Core\Role\UseCases\Queries\FetchAll\Fetcher as RoleFetcher;
use Illuminate\Support\Facades\Gate;

class UpdateController extends Controller
{
    /**
     * Показать форму редактирования пользователя
     */
    public function edit(Fetcher $fetcher, RoleFetcher $roleFetcher, string $userId): View
    {
        Gate::authorize('user.update', $userId);

        try {
            $query = new Query((int)$userId);
            $user = $fetcher->fetch($query);

            $roles = $roleFetcher->fetch()->results;
        } catch (UserNotFoundException) {
            throw new NotFoundHttpException('Пользователь не найден');
        }

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Обновить данные пользователя
     */
    public function update(UpdateUserRequest $request, Handler $handler, ChangeRoleHandler $changeRoleHandler, string $userId)
    {
        Gate::authorize('user.update', $userId);

        try {

            $command = new Command(
                id: (int)$userId,
                name: $request->get('name'),
                email: $request->get('email'),
                password: $request->get('password')
            );

            $handler->handle($command);

            // Изменяем роли, если они переданы
            $roles = $request->get('roles', []);
            if (!empty($roles)) {
                if (is_string($roles)) {
                    $roles = [$roles];
                }
                $changeRoleCommand = new ChangeRoleCommand((int)$userId, $roles);
                $changeRoleHandler->handle($changeRoleCommand);
            }

            return redirect()->route('admin.users.index')
                             ->with('success', "Пользователь '{$request->get('name')}' успешно создан");

        } catch (UserEmailAlreadyExistsException $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', $e->getMessage());

        } catch (UserSaveException $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', $e->getMessage());

        } catch (Exception) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Произошла непредвиденная ошибка при создании пользователя. Попробуйте позже.');
        }
    }


  /*
use App\Http\Controllers\Controller;
use App\Application\Core\User\UseCases\Commands\ChangeRoles\ChangeUserRolesCommand;
use Illuminate\Http\Request;
use Illuminate\Contracts\Bus\Dispatcher;

  public function __construct(private Dispatcher $dispatcher)
    {
        $this->middleware('can:manage-users-roles'); // middleware на право доступа
    }

  public function updateRoles(Request $request, int $userId)
    {
        $validated = $request->validate([
                                            'roles' => 'required|array',
                                            'roles.*' => 'string|exists:roles,slug',
                                        ]);

        $command = new ChangeUserRolesCommand($userId, $validated['roles']);
        $this->dispatcher->dispatch($command);

        return redirect()->back()->with('success', 'Роли пользователя обновлены');
    }*/
}
