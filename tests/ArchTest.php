<?php

use StanleyKinkelaar\LaravelBrowserSessionsLite;

arch('it will not use debugging functions')
    ->expect('StanleyKinkelaar\LaravelBrowserSessionsLite')
    ->not->toUse(['dd', 'dump', 'ray']);
