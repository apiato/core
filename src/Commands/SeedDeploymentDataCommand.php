<?php

namespace Apiato\Core\Commands;

use Apiato\Core\Abstracts\Commands\ConsoleCommand;
use Illuminate\Support\Facades\Config;

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
        $this->call('db:seed', [
            '--class' => Config::get('apiato.seeders.deployment'),
        ]);

        $this->info('Deployment Data Seeded Successfully.');
    }
}
