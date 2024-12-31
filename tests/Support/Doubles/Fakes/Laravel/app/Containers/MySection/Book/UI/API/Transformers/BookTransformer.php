<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Transformers;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Transformers\Transformer as ParentTransformer;

class BookTransformer extends ParentTransformer
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform(Book $book): array
    {
        return [
            'object' => $book->getResourceKey(),
            'id' => $book->getHashedKey(),
            'created_at' => $book->created_at,
            'updated_at' => $book->updated_at,
            'readable_created_at' => $book->created_at->diffForHumans(),
            'readable_updated_at' => $book->updated_at->diffForHumans(),
        ];
    }
}
