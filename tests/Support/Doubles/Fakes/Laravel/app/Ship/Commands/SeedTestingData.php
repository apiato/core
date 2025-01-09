<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Ship\Commands;

use Apiato\Abstract\Commands\Command;

class SeedTestingData extends Command
{
    protected $signature = 'apiato:seed-test';
    protected $description = 'Seed testing data';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => config('apiato.seeders.testing'),
        ]);

        $this->info('Testing Data Seeded Successfully.');
    }
}
