<?php

namespace Workbench\App\Containers\Identity\User\UI\API\Transformers;

use Apiato\Core\Transformers\Transformer;
use Apiato\Http\Resources\Collection;
use Apiato\Http\Resources\Item;
use League\Fractal\Resource\Primitive;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\MySection\Book\UI\API\Transformers\BookTransformer;
use Workbench\App\Containers\SocialInteraction\Comment\Models\Comment;

final class UserTransformer extends Transformer
{
    protected array $availableIncludes = [
        'parent',
        'children',
        'books',
    ];

    protected array $defaultIncludes = [
        'comments'
    ];

    public function transform(User $user): array
    {
        return [
            'type' => $user->getResourceKey(),
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

    public function includeComments(User $user): Collection
    {
        return $this->collection($user->comments, fn (Comment $comment) => $comment->toArray());
    }
}
