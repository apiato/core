<?php

namespace Apiato\Commands;

use Apiato\Abstract\Commands\Command;
use Apiato\Foundation\Support\PathHelper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class ListActions extends Command
{
    protected $signature = 'apiato:list:actions {--withfilename}';
    protected $description = 'List all Actions';

    public function __construct(ConsoleOutput $console)
    {
        parent::__construct();

        $this->console = $console;
    }

    public function handle(): void
    {
        foreach (PathHelper::getSectionNames() as $sectionName) {
            foreach (PathHelper::getSectionContainerNames($sectionName) as $containerName) {
                $this->console->writeln("<fg=yellow> [$containerName]</fg=yellow>");

                $directory = base_path('app/Containers/' . $sectionName . '/' . $containerName . '/Actions');

                if (File::isDirectory($directory)) {
                    $files = File::allFiles($directory);

                    foreach ($files as $file) {
                        $originalFileName = $file->getFilename();
                        $fileName = $originalFileName;
                        $fileName = Str::of($fileName)
                            ->replace('Action.php', '')
                            ->replace('.php', '')
                            ->replace('_', ' ')
                            ->headline();

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
}
