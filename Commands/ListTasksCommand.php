<?php

namespace Apiato\Core\Commands;

use Apiato\Core\Foundation\Facades\Apiato;
use App\Ship\Parents\Commands\ConsoleCommand;
use File;
use Symfony\Component\Console\Output\ConsoleOutput;

class ListTasksCommand extends ConsoleCommand
{

    /**
     * The name and signature of the console command.
     */
    protected string $signature = "apiato:list:tasks {--withfilename}";

    /**
     * The console command description.
     */
    protected string $description = "List all Tasks in the Application.";

    public function __construct(ConsoleOutput $console)
    {
        parent::__construct();

        $this->console = $console;
    }

    public function handle()
    {
        foreach (Apiato::getContainersNames() as $containerName) {

            $this->console->writeln("<fg=yellow> [$containerName]</fg=yellow>");

            $directory = base_path('app/Containers/' . $containerName . '/Tasks');

            if (File::isDirectory($directory)) {

                $files = File::allFiles($directory);

                foreach ($files as $action) {

                    // Get the file name as is
                    $fileName = $originalFileName = $action->getFilename();

                    // Remove the Task.php postfix from each file name
                    $fileName = str_replace('Task.php', '', $fileName);

                    // Further, remove the `.php', if the file does not end on 'Task.php'
                    $fileName = str_replace('.php', '', $fileName);

                    // UnCamelize the word and replace it with spaces
                    $fileName = Apiato::uncamelize($fileName);

                    // Check if flag exists
                    $includeFileName = '';
                    if ($this->option('withfilename')) {
                        $includeFileName = "<fg=red>($originalFileName)</fg=red>";
                    }

                    $this->console->writeln("<fg=green>  - $fileName</fg=green>  $includeFileName");
                }
            }
        }
    }

}
