<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Ship\Commands;

use Apiato\Abstract\Commands\Command;

class SeedDeploymentData extends Command
{
    protected $signature = 'apiato:seed-deploy';
    protected $description = 'Seed data for initial deployment';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => config('apiato.seeders.deployment'),
        ]);

        $this->info('Deployment Data Seeded Successfully.');
    }
}
