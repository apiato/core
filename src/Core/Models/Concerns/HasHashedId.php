<?php

declare(strict_types=1);

namespace Apiato\Core\Models\Concerns;

trait HasHashedId
{
    /**
     * Get the hashed key for the model or a specific field.
     *
     * Returns the hashed primary key by default.
     */
    public function getHashedKey(null|string $field = null): null|string|int
    {
        if (\is_null($field)) {
            $field = $this->getKeyName();
        }

        $attribute = $this->getAttribute($field);

        if (\is_null($attribute)) {
            return null;
        }

        if (config('apiato.hash-id')) {
            return hashids()->encodeOrFail($attribute);
        }

        return $this->getAttribute($field);
    }
}
