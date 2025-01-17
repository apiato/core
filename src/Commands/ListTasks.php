<?php

namespace Apiato\Commands;

use Apiato\Abstract\Commands\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ListTasks extends Command
{
    protected $signature = 'apiato:list:tasks {--withfilename}';
    protected $description = 'List all Tasks';

    public function handle(): void
    {
        collect(File::allFiles(app_path()))
            ->filter(static function (\SplFileInfo $file) {
                if (Str::contains($file->getRealPath(), shared_path())) {
                    return false;
                }

                return Str::contains($file->getFilename(), 'Task.php');
            })->groupBy(static function (\SplFileInfo $file) {
                return Str::of($file->getPath())
                    ->beforeLast(DIRECTORY_SEPARATOR)
                    ->afterLast(DIRECTORY_SEPARATOR)
                    ->value();
            })->each(function ($files, $group) {
                $this->comment("[{$group}]");

                foreach ($files as $file) {
                    $originalFileName = $file->getFilename();
                    $fileName = Str::of($originalFileName)
                        ->replace('Task.php', '')
                        ->replace('.php', '')
                        ->replace('_', ' ')
                        ->headline();

                    $includeFileName = '';
                    if ($this->option('withfilename')) {
                        $includeFileName = "<fg=red>({$originalFileName})</fg=red>";
                    }

                    $this->info("  - {$fileName}  {$includeFileName}");
                }
            });
    }
}
