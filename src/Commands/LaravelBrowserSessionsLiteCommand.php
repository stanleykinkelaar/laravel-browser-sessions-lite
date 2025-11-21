<?php

namespace StanleyKinkelaar\LaravelBrowserSessionsLite\Commands;

use Illuminate\Console\Command;

class LaravelBrowserSessionsLiteCommand extends Command
{
    public $signature = 'laravel-browser-sessions-lite';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
