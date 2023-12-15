<?php

namespace Apiato\Core\Commands;

use Apiato\Core\Abstracts\Commands\ConsoleCommand;
use Illuminate\Support\Facades\Config;

class SeedTestingDataCommand extends ConsoleCommand
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'apiato:seed-test';

    /**
     * The console command description.
     */
    protected $description = 'Seed testing data.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => Config::get('apiato.seeders.testing'),
        ]);

        $this->info('Testing Data Seeded Successfully.');
    }
}
