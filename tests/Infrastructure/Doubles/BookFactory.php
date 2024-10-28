<?php

namespace Apiato\Core\Tests\Infrastructure\Doubles;

use Apiato\Core\Abstracts\Factories\Factory as CoreFactory;

/**
 * @template TModel of Book
 *
 * @extends CoreFactory<TModel>
 */
class BookFactory extends CoreFactory
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
