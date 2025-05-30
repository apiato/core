<?php

namespace Apiato\Console\Commands;

use Apiato\Core\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

final class ListTasks extends Command
{
    protected $signature = 'apiato:list:tasks {--with-file-name}';
    protected $description = 'List all Tasks';

    public function handle(): void
    {
        collect(File::allFiles(app_path()))
            ->filter(static function (SplFileInfo $file) {
                if (Str::contains($file->getRealPath(), shared_path())) {
                    return false;
                }

                return Str::contains($file->getFilename(), 'Task.php');
            })->groupBy(static fn (SplFileInfo $file) => Str::of($file->getPath())
                ->beforeLast(DIRECTORY_SEPARATOR)
                ->afterLast(DIRECTORY_SEPARATOR)
                ->value())->each(function (Collection $files, string $group): void {
                    $this->comment("[{$group}]");

                    /** @var SplFileInfo $file */
                    foreach ($files as $file) {
                        $originalFileName = $file->getFilename();
                        $fileName = Str::of($originalFileName)
                            ->replace('Task.php', '')
                            ->replace('.php', '')
                            ->replace('_', ' ')
                            ->headline();

                        if ($this->option('with-file-name')) {
                            $includeFileName = "<fg=red>({$originalFileName})</fg=red>";
                            $this->info(" - {$fileName} {$includeFileName}");
                        } else {
                            $this->info(" - {$fileName}");
                        }
                    }
                });
    }
}
