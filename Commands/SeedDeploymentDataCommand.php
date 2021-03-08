<?php

namespace Apiato\Core\Commands;

use App\Ship\Parents\Commands\ConsoleCommand;
use App\Ship\Seeders\SeedDeploymentData;

class SeedDeploymentDataCommand extends ConsoleCommand
{

    /**
     * The name and signature of the console command.
     */
    protected string $signature = "apiato:seed-deploy";

    /**
     * The console command description.
     */
    protected string $description = "Seed data for initial deployment.";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->call('db:seed', [
            '--class' => SeedDeploymentData::class
        ]);

        $this->info('Deployment Data Seeded Successfully.');
    }

}
