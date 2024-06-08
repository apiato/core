<?php

namespace Apiato\Core\Macros\Collection;

use Illuminate\Support\Collection;
use Vinkla\Hashids\Facades\Hashids;

class ContainsHashedId {
    public function __invoke(): callable
    {
        return
            /**
             * Check if the given hashed id exists in the collection
             */
            function (string $hashedId, string $key = 'id'): bool
            {
                /** @var Collection $this */
                return $this->contains($key, Hashids::decode($hashedId)[0]);
            };
    }
}
