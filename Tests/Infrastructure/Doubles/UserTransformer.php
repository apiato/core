<?php

namespace Apiato\Core\Tests\Infrastructure\Doubles;

use Apiato\Core\Abstracts\Transformers\Transformer;

class UserTransformer extends Transformer
{
    public function transform(User $user): array
    {
        return [
            'id' => $user->getHashedKey(),
            'parent_id' => $user->getHashedKey('parent_id'),
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}
