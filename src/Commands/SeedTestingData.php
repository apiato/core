<?php

namespace Apiato\Commands;

use Apiato\Abstract\Commands\Command;

class SeedTestingData extends Command
{
    protected $signature = 'apiato:seed-test';
    protected $description = 'Seed testing data';

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
