<?php

namespace App\Providers;

use App\Policies\UserPolicy;
use App\Policies\CommentPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\NewsPolicy;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Passport::tokensExpireIn(CarbonInterval::days(15));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
        Passport::personalAccessTokensExpireIn(CarbonInterval::months(6));

        $policies = [
            'user' => UserPolicy::class,
            'news' => NewsPolicy::class,
            'comment' => CommentPolicy::class,
            'category' => CategoryPolicy::class,
        ];

        $abilities = ['viewAny', 'view', 'create', 'update', 'delete'];

        foreach ($policies as $key => $policyClass) {
            foreach ($abilities as $ability) {
                Gate::define("{$key}.{$ability}", [$policyClass, $ability]);
            }
        }
    }
}
