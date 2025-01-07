<?php

use Apiato\Foundation\Support\Traits\FactoryDiscovery;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Data\Factories\BookFactory;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;

describe(class_basename(FactoryDiscovery::class), function (): void {
    it('can locate the factory of a model using different call styles', function (): void {
        $usingModel = Book::factory()->createOne();
        $usingFactory = BookFactory::new()->createOne();

        expect($usingModel)->toBeInstanceOf(Book::class)
            ->and($usingFactory)->toBeInstanceOf(Book::class);
    });
})->covers(FactoryDiscovery::class);
