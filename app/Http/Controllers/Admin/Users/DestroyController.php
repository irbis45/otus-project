<?php

namespace App\Http\Controllers\Admin\Users;

use App\Application\Core\User\Exceptions\UserNotFoundException;
use App\Application\Core\User\UseCases\Commands\Delete\Command;
use App\Application\Core\User\UseCases\Commands\Delete\Handler;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Gate;

class DestroyController extends Controller
{
    /**
     * Удалить пользователя
     */
    public function __invoke(Handler $handler, string $userId): RedirectResponse
    {
        Gate::authorize('user.delete', $userId);

        try {
            $command = new Command((int)$userId);
            $handler->handle($command);
        } catch (UserNotFoundException) {
            throw new NotFoundHttpException('Пользователь не найден');
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Пользователь успешно удален");
    }
}
