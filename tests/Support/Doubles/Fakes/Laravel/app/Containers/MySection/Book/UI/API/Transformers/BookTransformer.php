<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Transformers;

use Apiato\Abstract\Transformers\Transformer;
use League\Fractal\Resource\Item;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Support\UserTransformer;

class BookTransformer extends Transformer
{
    protected array $availableIncludes = [
        'author',
    ];

    protected array $defaultIncludes = [];

    public function transform(Book $book): array
    {
        return [
            'object' => $book->getResourceKey(),
            'id' => $book->getHashedKey(),
            'title' => $book->title,
            'author' => $book->author->name,
            'created_at' => $book->created_at,
            'updated_at' => $book->updated_at,
        ];
    }

    public function includeAuthor(Book $book): Item
    {
        return $this->item($book->author, new UserTransformer());
    }
}
