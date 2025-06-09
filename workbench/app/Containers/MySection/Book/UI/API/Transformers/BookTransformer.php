<?php

declare(strict_types=1);

namespace Workbench\App\Containers\MySection\Book\UI\API\Transformers;

use Apiato\Core\Transformers\Transformer;
use League\Fractal\Resource\Item;
use Workbench\App\Containers\Identity\User\UI\API\Transformers\UserTransformer;
use Workbench\App\Containers\MySection\Book\Models\Book;

class BookTransformer extends Transformer
{
    protected array $availableIncludes = [
        'author',
    ];

    protected array $defaultIncludes = [];

    public function transform(Book $book): array
    {
        return [
            'type'       => $book->getResourceKey(),
            'id'         => $book->getHashedKey(),
            'title'      => $book->title,
            'author'     => $book->author?->name,
            'created_at' => $book->created_at,
            'updated_at' => $book->updated_at,
        ];
    }

    public function includeAuthor(Book $book): Item
    {
        return $this->item($book->author, new UserTransformer());
    }
}
