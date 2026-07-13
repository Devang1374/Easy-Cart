<?php

namespace App\Console\Commands\Support;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:clear-cache')]
#[Description('Command description')]
class ClearCache extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
