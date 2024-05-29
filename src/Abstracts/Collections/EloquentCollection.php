<?php

namespace Apiato\Core\Abstracts\Collections;

use Illuminate\Database\Eloquent\Collection;
use Vinkla\Hashids\Facades\Hashids;

abstract class EloquentCollection extends Collection
{
    /**
     * Check if the given hashed id exists in the collection
     */
    public function containsHashedId(string $hashedId, string $key = 'id'): bool
    {
        return $this->contains($key, Hashids::decode($hashedId)[0]);
    }
}
