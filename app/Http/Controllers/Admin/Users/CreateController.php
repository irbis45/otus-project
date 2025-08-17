<?php

namespace App\Http\Controllers\Admin\Users;

use App\Application\Core\Role\Enums\Role as RoleEnum;
use App\Application\Core\Role\UseCases\Commands\ChangeUserRole\Command as ChangeRoleCommand;
use App\Application\Core\Role\UseCases\Commands\ChangeUserRole\Handler as ChangeRoleHandler;
use App\Application\Core\User\Exceptions\UserEmailAlreadyExistsException;
use App\Application\Core\User\Exceptions\UserSaveException;
use App\Application\Core\User\UseCases\Commands\Create\Command;
use App\Application\Core\User\UseCases\Commands\Create\Handler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateUserRequest;
use App\Application\Core\Role\UseCases\Queries\FetchAll\Fetcher as RoleFetcher;
use Exception;
use Illuminate\Support\Facades\Gate;

class CreateController extends Controller
{
    /**
     * Показать форму создания пользователя
     */
    public function create(RoleFetcher $roleFetcher)
    {
        Gate::authorize('user.create');

        $roles = $roleFetcher->fetch()->results;

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Сохранить нового пользователя
     */
    public function store(CreateUserRequest $request, Handler $handler, ChangeRoleHandler $changeRoleHandler)
    {
        Gate::authorize('user.create');

        try {
            $command = new Command(
                name: $request->get('name'),
                email: $request->get('email'),
                password: $request->get('password'),
            );

            $userDTO = $handler->handle($command);

            // Получаем роли из запроса, если они есть
            $roles = $request->get('roles', [RoleEnum::USER->value]);
            if (is_string($roles)) {
                $roles = [$roles];
            }

            $changeRoleCommand = new ChangeRoleCommand($userDTO->id, $roles);
            $changeRoleHandler->handle($changeRoleCommand);

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

        } catch (Exception $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Произошла непредвиденная ошибка при создании пользователя. Попробуйте позже.');
        }
    }
}
