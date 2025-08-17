<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\View\Composers\CategoryComposer;
use App\Http\View\Composers\AdminMenuComposer;
use App\Http\View\Composers\UserComposer;
use App\Http\View\Composers\PublicMenuComposer;

class ViewServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.app', 'home', 'news.show', 'news.by_category', 'news.search'], CategoryComposer::class);
        View::composer('admin.menu.index', AdminMenuComposer::class);
        View::composer('partials.menu.top.index', PublicMenuComposer::class);
        View::composer('*', UserComposer::class);
    }
}
