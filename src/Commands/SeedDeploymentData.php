<?php

namespace Apiato\Commands;

use Apiato\Abstract\Commands\Command;

class SeedDeploymentData extends Command
{
    protected $signature = 'apiato:seed-deploy';
    protected $description = 'Seed data for initial deployment';

    public function handle(): void
    {
        if (!config('apiato.seeders.deployment')) {
            $this->error('No Deployment Seeder Found, Please Check Your Config File.');

            return;
        }

        if (!class_exists(config('apiato.seeders.deployment'))) {
            $this->error('Deployment Seeder Class Not Found.');

            return;
        }

        $this->call('db:seed', [
            '--class' => config('apiato.seeders.deployment'),
        ]);

        $this->info('Deployment Data Seeded Successfully.');
    }
}
