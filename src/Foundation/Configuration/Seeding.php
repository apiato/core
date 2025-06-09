<?php

declare(strict_types=1);

namespace Apiato\Foundation\Configuration;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * @template TSeeder of Seeder
 */
final class Seeding
{
    private static \Closure $seederSorter;

    /** @var string[] */
    private array $paths = [];

    public function __construct()
    {
        $this->sortUsing(
            static fn (
                array $classMapGroupedByDirectory,
            ) => collect($classMapGroupedByDirectory)
                ->flatMap(
                    static fn (array $directoryClassMap): Collection => collect($directoryClassMap)
                        ->sortBy(
                            static fn (string $path, string $class): string => substr(
                                $class,
                                strpos($class, '_') + 1,
                            ),
                        ),
                )->keys()
                ->toArray(),
        );
    }

    /**
     * @param \Closure(array<array<class-string<TSeeder>, non-empty-string>>): class-string<TSeeder> $callback
     */
    public function sortUsing(\Closure $callback): self
    {
        self::$seederSorter = $callback;

        return $this;
    }

    /**
     * @return class-string<TSeeder>[]
     */
    public function seeders(): array
    {
        $classMapGroupedByDirectory = [];
        foreach ($this->paths as $path) {
            $classMapGroupedByDirectory[] = ClassMapGenerator::createMap($path);
        }

        return $this->getSortedFiles($classMapGroupedByDirectory);
    }

    public function loadFrom(string ...$paths): self
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * @param array<array<class-string<TSeeder>, non-empty-string>> $classMapGroupedByDirectory
     *
     * @return class-string<TSeeder>[]
     */
    private function getSortedFiles(array $classMapGroupedByDirectory): array
    {
        return app()->call(self::$seederSorter, ['classMapGroupedByDirectory' => $classMapGroupedByDirectory]);
    }
}
