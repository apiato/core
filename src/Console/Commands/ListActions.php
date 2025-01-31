<?php

namespace Apiato\Console\Commands;

use Apiato\Abstract\Commands\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class ListActions extends Command
{
    protected $signature = 'apiato:list:actions {--with-file-name}';
    protected $description = 'List all Actions';

    public function handle(): void
    {
        collect(File::allFiles(app_path()))
            ->filter(static function (\SplFileInfo $file) {
                if (Str::contains($file->getRealPath(), shared_path())) {
                    return false;
                }

                return Str::contains($file->getFilename(), 'Action.php');
            })->groupBy(static fn (\SplFileInfo $file) => Str::of($file->getPath())
                ->beforeLast(DIRECTORY_SEPARATOR)
                ->afterLast(DIRECTORY_SEPARATOR)
                ->value())->each(function ($files, $group): void {
                    $this->comment("[{$group}]");

                    foreach ($files as $file) {
                        $originalFileName = $file->getFilename();
                        $fileName = Str::of($originalFileName)
                            ->replace('Action.php', '')
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
