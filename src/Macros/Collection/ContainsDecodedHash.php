<?php

namespace Apiato\Core\Macros\Collection;

use Illuminate\Support\Collection;
use Vinkla\Hashids\Facades\Hashids;

class ContainsDecodedHash {
    public function __invoke(): callable
    {
        return
            /**
             * Decodes a hashed value and checks if the decoded value exists in the collection under the specified key.
             */
            function (string $hashedValue, string $key = 'id'): bool
            {
                /** @var Collection $this */
                return $this->contains($key, Hashids::decode($hashedValue)[0]);
            };
    }
}
