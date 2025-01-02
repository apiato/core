<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Data\Factories;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\Identity\User\Data\Factories\UserFactory;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Book
 *
 * @extends ParentFactory<TModel>
 */
class BookFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence,
            'author_id' => UserFactory::new(),
        ];
    }
}
