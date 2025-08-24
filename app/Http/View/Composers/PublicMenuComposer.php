<?php

declare(strict_types=1);

namespace App\Http\View\Composers;

use Illuminate\Auth\AuthManager;
use Illuminate\View\View;
use App\Services\MenuBuilder;

class PublicMenuComposer
{

    public function __construct(private MenuBuilder $menuBuilder, private AuthManager $authManager)
    {}

    public function compose(View $view)
    {
        $user = $this->authManager->guard()->user();
        $menu = config('menu.public');
        $menuItems = $this->menuBuilder->build($menu, $user);

        $view->with('publicMenuItems', $menuItems);
    }
}
