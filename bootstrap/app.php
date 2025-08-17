<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\WarmCacheCommand;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\AdminPanelAccessMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'auth', 'admin_panel_access'])
                 ->prefix('admin_panel')
                 ->name('admin.')
                 ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias(
            [
                'role' => RoleMiddleware::class,
                'permission' => PermissionMiddleware::class,
                'admin_panel_access' => AdminPanelAccessMiddleware::class,
            ]
        );
    })
    ->withSchedule(function (Schedule $schedule) {

        $schedule->command(WarmCacheCommand::class, ['news'])
                 ->everyFifteenMinutes()
                 ->onOneServer();

        $schedule->command(WarmCacheCommand::class, ['categories'])
                 ->everyThirtyMinutes()
                 ->onOneServer();

        $schedule->command('cache:clear')
                 ->daily()
                 ->onOneServer();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
