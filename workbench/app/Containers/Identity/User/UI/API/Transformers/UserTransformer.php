<?php

namespace Workbench\App\Containers\Identity\User\UI\API\Transformers;

use Apiato\Abstract\Transformers\Transformer;
use Apiato\Support\Resources\Collection;
use Apiato\Support\Resources\Item;
use League\Fractal\Resource\Primitive;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\MySection\Book\UI\API\Transformers\BookTransformer;

final class UserTransformer extends Transformer
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
        return $this->nullableItem($user->parent, new self());
    }

    public function includeChildren(User $user): Collection
    {
        return $this->collection($user->children, new self());
    }

    public function includeBooks(User $user): Collection
    {
        return $this->collection($user->books, new BookTransformer());
    }
}
