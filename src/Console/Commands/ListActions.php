<?php

declare(strict_types=1);

namespace Apiato\Console\Commands;

use Apiato\Core\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

final class ListActions extends Command
{
    /** @var string */
    protected $signature = 'apiato:list:actions {--with-file-name}';

    /** @var string */
    protected $description = 'List all Actions';

    public function handle(): void
    {
        collect(File::allFiles(app_path()))
            ->filter(static function (SplFileInfo $file) {
                if (Str::contains($file->getRealPath(), shared_path())) {
                    return false;
                }

                return Str::contains($file->getFilename(), 'Action.php');
            })->groupBy(static fn (SplFileInfo $file) => Str::of($file->getPath())
                ->beforeLast(DIRECTORY_SEPARATOR)
                ->afterLast(DIRECTORY_SEPARATOR)
                ->value())->each(function (Collection $files, string $group): void {
                    $this->comment(\sprintf('[%s]', $group));

                    /** @var SplFileInfo $file */
                    foreach ($files as $file) {
                        $originalFileName = $file->getFilename();
                        $fileName = Str::of($originalFileName)
                            ->replace('Action.php', '')
                            ->replace('.php', '')
                            ->replace('_', ' ')
                            ->headline();

                        if ($this->option('with-file-name')) {
                            $includeFileName = \sprintf('<fg=red>(%s)</fg=red>', $originalFileName);
                            $this->info(\sprintf(' - %s %s', $fileName, $includeFileName));
                        } else {
                            $this->info(' - ' . $fileName);
                        }
                    }
                });
    }
}
