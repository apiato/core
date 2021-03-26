<?php

namespace Apiato\Core\Commands;

use App\Ship\Parents\Commands\ConsoleCommand;
use App\Ship\Seeders\SeedTestingData;

class SeedTestingDataCommand extends ConsoleCommand
{

    /**
     * The name and signature of the console command.
     */
    protected string $signature = "apiato:seed-test";

    /**
     * The console command description.
     */
    protected string $description = "Seed testing data.";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->call('db:seed', [
            '--class' => SeedTestingData::class
        ]);

        $this->info('Testing Data Seeded Successfully.');
    }

}
