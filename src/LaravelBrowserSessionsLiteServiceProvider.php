<?php

namespace StanleyKinkelaar\LaravelBrowserSessionsLite;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use StanleyKinkelaar\LaravelBrowserSessionsLite\Commands\LaravelBrowserSessionsLiteCommand;

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
            ->hasMigration('create_browser_sessions_table')
            ->hasCommand(LaravelBrowserSessionsLiteCommand::class);
    }
}
