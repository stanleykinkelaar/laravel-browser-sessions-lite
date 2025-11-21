<?php

// config for StanleyKinkelaar/LaravelBrowserSessionsLite
return [

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware that should be applied to the browser sessions routes.
    | By default, it uses 'web' and 'auth' middleware to ensure the user
    | is authenticated before managing their browser sessions.
    |
    */

    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URI prefix for all browser sessions routes.
    | Default: 'user' (results in /user/browser-sessions)
    |
    */

    'prefix' => 'user',

];
