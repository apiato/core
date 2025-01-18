<?php

use Apiato\Foundation\Configuration\FactoryDiscovery;
use Workbench\App\Containers\MySection\Book\Data\Factories\BookFactory;
use Workbench\App\Containers\MySection\Book\Models\Book;

describe(class_basename(FactoryDiscovery::class), function (): void {
    it('can discover using default factory name resolver', function (): void {
        $configuration = new FactoryDiscovery();

        expect($configuration->resolveFactoryName(Book::class))
            ->toBe(BookFactory::class);
    });

    it('can set custom factory name resolver', function (): void {
        $configuration = new FactoryDiscovery();

        $configuration->resolveFactoryNameUsing(static fn (string $modelName): string => BookFactory::class);

        expect($configuration->resolveFactoryName('test'))
            ->toBe(BookFactory::class);
    });

    it('returns null if cannot resolve factory', function (): void {
        $configuration = new FactoryDiscovery();

        $configuration->resolveFactoryNameUsing(static fn (string $modelName): string => $modelName);

        expect($configuration->resolveFactoryName('test'))
            ->toBeNull();
    });
})->covers(FactoryDiscovery::class);
