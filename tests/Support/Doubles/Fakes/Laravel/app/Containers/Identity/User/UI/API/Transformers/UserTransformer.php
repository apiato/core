<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\Identity\User\UI\API\Transformers;

use Apiato\Abstract\Transformers\Transformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Primitive;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\Identity\User\Models\User;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Transformers\BookTransformer;

class UserTransformer extends Transformer
{
    protected array $availableIncludes = [
        'parent',
        'children',
        'books',
    ];

    protected array $defaultIncludes = [];

    public function transform(User $user): array
    {
        return [
            'object' => $user->getResourceKey(),
            'id' => $user->getHashedKey(),
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    public function includeParent(User $user): Primitive|Item
    {
        return $this->nullableItem($user->parent, new static());
    }

    public function includeChildren(User $user): Collection
    {
        return $this->collection($user->children, new static());
    }

    public function includeBooks(User $user): Collection
    {
        return $this->collection($user->books, new BookTransformer());
    }
}
