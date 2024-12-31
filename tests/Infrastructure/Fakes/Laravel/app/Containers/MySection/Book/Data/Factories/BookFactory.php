<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Data\Factories;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Book
 *
 * @extends ParentFactory<TModel>
 */
class BookFactory extends ParentFactory
{
    /** @var class-string<TModel> */
    protected $model = Book::class;

    public function definition(): array
    {
        return [];
    }
}
