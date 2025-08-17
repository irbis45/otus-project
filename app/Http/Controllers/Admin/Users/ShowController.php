<?php

namespace App\Http\Controllers\Admin\Users;

use App\Application\Core\User\Exceptions\UserNotFoundException;
use App\Application\Core\User\UseCases\Queries\FetchyById\Fetcher;
use App\Application\Core\User\UseCases\Queries\FetchyById\Query;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Gate;

class ShowController extends Controller
{
    /**
     * Показать детали пользователя
     */
    public function __invoke(Fetcher $fetcher, string $userId): View
    {
        Gate::authorize('user.view', $userId);

        try {
            $query = new Query((int)$userId);
            $user = $fetcher->fetch($query);

        } catch (UserNotFoundException) {
            throw new NotFoundHttpException('Пользователь не найден');
        }

        return view('admin.users.show', compact('user'));
    }
}
