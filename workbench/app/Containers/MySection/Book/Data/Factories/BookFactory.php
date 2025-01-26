<?php

namespace Workbench\App\Containers\MySection\Book\Data\Factories;

use Workbench\App\Containers\Identity\User\Data\Factories\UserFactory;
use Workbench\App\Containers\MySection\Book\Models\Book;
use Workbench\App\Ship\Parents\Factories\Factory as ParentFactory;

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
