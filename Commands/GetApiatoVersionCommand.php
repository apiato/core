<?php

namespace Apiato\Core\Commands;

use Apiato\Core\Foundation\Apiato;
use App\Ship\Parents\Commands\ConsoleCommand;

class GetApiatoVersionCommand extends ConsoleCommand
{

    /**
     * The name and signature of the console command.
     */
    protected $signature = "apiato";

    /**
     * The console command description.
     */
    protected string $description = "Display the current Apiato version.";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info(Apiato::VERSION);
    }

}
