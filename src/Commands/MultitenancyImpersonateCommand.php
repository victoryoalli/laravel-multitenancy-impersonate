<?php

namespace VictorYoalli\MultitenancyImpersonate\Commands;

use Illuminate\Console\Command;

class MultitenancyImpersonateCommand extends Command
{
    public $signature = 'multitenancy-impersonate';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
