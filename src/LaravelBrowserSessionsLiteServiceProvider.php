<?php

namespace StanleyKinkelaar\LaravelBrowserSessionsLite;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use StanleyKinkelaar\LaravelBrowserSessionsLite\Repositories\SessionRepository;
use StanleyKinkelaar\LaravelBrowserSessionsLite\Services\BrowserSessions;

class LaravelBrowserSessionsLiteServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-browser-sessions-lite')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web');
    }

    public function packageRegistered(): void
    {
        // Register repository
        $this->app->singleton(SessionRepository::class);

        // Register service
        $this->app->singleton(BrowserSessions::class, function ($app) {
            return new BrowserSessions($app->make(SessionRepository::class));
        });

        // Bind facade accessor
        $this->app->singleton(LaravelBrowserSessionsLite::class, function ($app) {
            return $app->make(BrowserSessions::class);
        });
    }
}
