<?php

namespace Apiato\Foundation\Configuration;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

final class Seeding
{
    protected static \Closure $seederSorter;
    protected array $paths = [];

    public function __construct()
    {
        $this->sortUsing(
            static fn (
                array $classMapGroupedByDirectory,
            ) => collect($classMapGroupedByDirectory)
                ->flatMap(
                    static fn (array $directoryClassMap): Collection => collect($directoryClassMap)
                        ->sortBy(
                            static fn ($path, $class): string => substr(
                                (string) $class,
                                strpos((string) $class, '_') + 1,
                            ),
                        ),
                )->keys()
                ->toArray(),
        );
    }

    public function sortUsing(\Closure $callback): self
    {
        self::$seederSorter = $callback;

        return $this;
    }

    /**
     * @return class-string<Seeder>[]
     */
    public function seeders(): array
    {
        $classMapGroupedByDirectory = [];
        foreach ($this->paths as $path) {
            $classMapGroupedByDirectory[] = ClassMapGenerator::createMap($path);
        }

        return $this->getSortedFiles($classMapGroupedByDirectory);
    }

    /**
     * @param array<array-key, array<string, string>> $classMapGroupedByDirectory
     *
     * @return array<array-key, string>
     */
    private function getSortedFiles(array $classMapGroupedByDirectory): array
    {
        return app()->call(self::$seederSorter, ['classMapGroupedByDirectory' => $classMapGroupedByDirectory]);
    }

    public function loadFrom(string ...$paths): self
    {
        $this->paths = $paths;

        return $this;
    }
}
