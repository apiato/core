<?php

namespace Apiato\Core\Commands;

use Apiato\Core\Abstracts\Commands\ConsoleCommand;

class SeedDeploymentDataCommand extends ConsoleCommand
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'apiato:seed-deploy';

    /**
     * The console command description.
     */
    protected $description = 'Seed data for initial deployment.';

    public function __construct()
    {
        parent::__construct();
    }

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
