<?php

declare(strict_types=1);

namespace App\Http\View\Composers;

use Illuminate\Auth\AuthManager;
use Illuminate\View\View;
use App\ViewModels\UserViewModel;

class UserComposer
{
    protected AuthManager $auth;

    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }

    public function compose(View $view)
    {
        $user = $this->auth->guard()->user();

        $view->with('authUser', $user ? new UserViewModel($user) : null);
    }
}
