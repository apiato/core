<?php

namespace Apiato\Core\Commands;

use Apiato\Core\Abstracts\Commands\ConsoleCommand;

/**
 * Class SeedTestingDataCommand
 *
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
class SeedTestingDataCommand extends ConsoleCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "apiato:seed-test";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Seed testing data.";

    /**
     * SeedTestingDataCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handle the command
     */
    public function handle()
    {
        $this->call('db:seed', [
            '--class' => 'App\Ship\Seeders\SeedTestingData'
        ]);

        $this->info('Testing Data Seeded Successfully.');
    }

}
