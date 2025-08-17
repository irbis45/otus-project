<?php

namespace App\Providers;

use App\Application\Contracts\CacheInterface;
use App\Application\Contracts\PasswordHasherInterface;
use App\Application\Contracts\TelegramServiceInterface;
use App\Application\Contracts\ViewedNewsStorageInterface;
use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;
use App\Application\Core\Comment\Repositories\CommentRepositoryInterface;
use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Application\Core\News\UseCases\Commands\TrackView\Command as TrackViewCommand;
use App\Application\Core\News\UseCases\Commands\TrackView\Handler as TrackViewHandler;
use App\Application\Core\Role\Repositories\RoleRepositoryInterface;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Application\OAuth\AuthService as OAuthAuthService;
use App\Application\OAuth\Contracts\AuthServiceInterface as OAuthAuthServiceInterface;
use App\Application\OAuth\Contracts\OAuthRefreshTokenRepositoryInterface;
use App\Application\OAuth\Contracts\OAuthTokenRepositoryInterface;
use App\Infrastructure\Cache\LaravelCache;
use App\Infrastructure\Eloquent\Repositories\Categories\CategoryRepository;
use App\Infrastructure\Eloquent\Repositories\Comments\CommentRepository;
use App\Infrastructure\Eloquent\Repositories\News\NewsRepository;
use App\Infrastructure\Eloquent\Repositories\Roles\RoleRepository;
use App\Infrastructure\Eloquent\Repositories\Users\UserRepository;
use App\Infrastructure\Notification\Telegram\TelegramService;
use App\Infrastructure\Oauth\PassportRefreshTokenRepositoryAdapter;
use App\Infrastructure\Oauth\PassportTokenRepositoryAdapter;
use App\Infrastructure\PasswordHasher\LaravelPasswordHasher;
use App\Infrastructure\ViewedNews\SessionViewedNewsStorage;
use Illuminate\Bus\Dispatcher;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            RoleRepositoryInterface::class,
            RoleRepository::class
        );

        $this->app->bind(
            CommentRepositoryInterface::class,
            CommentRepository::class
        );

        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );

        $this->app->bind(
            NewsRepositoryInterface::class,
            NewsRepository::class
        );

        $this->app->bind(
            PasswordHasherInterface::class,
            LaravelPasswordHasher::class
        );

        $this->app->bind(
            CacheInterface::class,
            LaravelCache::class
        );

        $this->app->bind(
            TelegramServiceInterface::class,
            TelegramService::class
        );

        $this->app->bind(
            ViewedNewsStorageInterface::class,
            SessionViewedNewsStorage::class
        );

        $this->app->bind(OAuthAuthServiceInterface::class, OAuthAuthService::class);

        $this->app->bind(OAuthTokenRepositoryInterface::class, PassportTokenRepositoryAdapter::class);
        $this->app->bind(OAuthRefreshTokenRepositoryInterface::class, PassportRefreshTokenRepositoryAdapter::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // Получаем Dispatcher из контейнера
        $dispatcher = $this->app->make(Dispatcher::class);
        $dispatcher->map([
                             TrackViewCommand::class => TrackViewHandler::class,
                         ]);

    }
}
