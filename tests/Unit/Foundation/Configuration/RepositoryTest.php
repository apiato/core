<?php

use Apiato\Foundation\Configuration\Repository;
use Workbench\App\Containers\MySection\Book\Data\Repositories\BookRepository;
use Workbench\App\Containers\MySection\Book\Models\Book;
use Workbench\App\Containers\MySection\MultiWord\Data\Repositories\MultiWordRepository;
use Workbench\App\Containers\MySection\MultiWord\Models\MultiWord;

describe(class_basename(Repository::class), function (): void {
    it('can resolve the model name using the default resolver', function (): void {
        $configuration = new Repository();

        expect($configuration->resolveModelName(BookRepository::class))
            ->toBe(Book::class)
            ->and(static fn (): string => $configuration->resolveModelName('test'))
            ->toThrow(RuntimeException::class);

        expect($configuration->resolveModelName(MultiWordRepository::class))
            ->toBe(MultiWord::class)
            ->and(static fn (): string => $configuration->resolveModelName('test'))
            ->toThrow(RuntimeException::class);
    });

    it('can set custom model name resolver', function (): void {
        $configuration = new Repository();

        $configuration->resolveModelNameUsing(static fn (string $repositoryName): string => Book::class);

        expect($configuration->resolveModelName('test'))
            ->toBe(Book::class);
    });
})->covers(Repository::class);
