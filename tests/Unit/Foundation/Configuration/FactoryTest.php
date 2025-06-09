<?php

declare(strict_types=1);

use Apiato\Foundation\Configuration\Factory;
use Workbench\App\Containers\MySection\Book\Data\Factories\BookFactory;
use Workbench\App\Containers\MySection\Book\Models\Book;

describe(class_basename(Factory::class), function (): void {
    it('can resolve the factory name using the default resolver', function (): void {
        $configuration = new Factory();

        expect($configuration->resolveFactoryName(Book::class))
            ->toBe(BookFactory::class)
            ->and($configuration->resolveFactoryName('test'))
            ->toBeNull();
    });

    it('can set custom factory name resolver', function (): void {
        $configuration = new Factory();

        $configuration->resolveFactoryNameUsing(static fn (string $modelName): string => BookFactory::class);

        expect($configuration->resolveFactoryName('test'))
            ->toBe(BookFactory::class);
    });
})->covers(Factory::class);
