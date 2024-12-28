<?php

namespace Apiato\Core\Commands;

use Apiato\Core\Abstracts\Console\Commands\Command;

class SeedTestingData extends Command
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
        if (!config('apiato.seeders.testing')) {
            $this->error('No Testing Seeder Found, Please Check Your Config File.');

            return;
        }

        if (!class_exists(config('apiato.seeders.testing'))) {
            $this->error('Testing Seeder Class Not Found.');

            return;
        }

        $this->call('db:seed', [
            '--class' => config('apiato.seeders.testing'),
        ]);

        $this->info('Testing Data Seeded Successfully.');
    }
}
