<?php

namespace Apiato\Foundation\Configuration;

use Apiato\Foundation\Support\PathHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class Seeding
{
    protected static \Closure $seederSorter;
    protected array $paths = [];

    public function __construct()
    {
        $this->sortUsing(
            static fn (Collection $filePathsByDirectory) => $filePathsByDirectory
                ->flatMap(
                    static fn ($files): Collection => $files
                        ->sortBy(
                            static fn ($fineName) => substr(
                                $fineName,
                                strpos($fineName, '_') + 1,
                            ),
                        ),
                ),
        );
    }

    public function sortUsing(callable $callback): self
    {
        self::$seederSorter = $callback;

        return $this;
    }

    /**
     * @return class-string<Seeder>[]
     */
    public function seeders(): array
    {
        $filePathsByDirectory = collect($this->paths)
            ->map(
                static function ($path): Collection {
                    return collect(File::files($path));
                },
            );

        return $this->getSortedFiles($filePathsByDirectory)
            ->filter(static fn (\SplFileInfo $file): bool => 'php' === $file->getExtension())
            ->map(
                static fn (\SplFileInfo $file): string => PathHelper::getFQCNFromFile(
                    $file->getPathname(),
                ),
            )->toArray();
    }

    /**
     * @param Collection<int, Collection<int, string>> $filePathsByDirectory
     *
     * @return Collection<int, string>
     */
    private function getSortedFiles(Collection $filePathsByDirectory): Collection
    {
        return app()->call(self::$seederSorter, compact('filePathsByDirectory'));
    }

    public function loadFrom(string ...$paths): self
    {
        $this->paths = $paths;

        return $this;
    }
}
