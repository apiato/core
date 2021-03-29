<?php

namespace Apiato\Core\Commands;

use Apiato\Core\Abstracts\Commands\ConsoleCommand;
use App\Ship\Seeders\SeedTestingData;

class SeedTestingDataCommand extends ConsoleCommand
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = "apiato:seed-test";

    /**
     * The console command description.
     */
    protected $description = "Seed testing data.";

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
