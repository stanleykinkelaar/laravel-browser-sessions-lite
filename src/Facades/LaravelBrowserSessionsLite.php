<?php

namespace StanleyKinkelaar\LaravelBrowserSessionsLite\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \StanleyKinkelaar\LaravelBrowserSessionsLite\LaravelBrowserSessionsLite
 */
class LaravelBrowserSessionsLite extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \StanleyKinkelaar\LaravelBrowserSessionsLite\LaravelBrowserSessionsLite::class;
    }
}
