<?php

namespace Apiato\Abstract\Models;

use Apiato\Contracts\HasResourceKey;
use Apiato\Foundation\Support\Traits\Model\CanOwn;
use Apiato\Foundation\Support\Traits\Model\HashedRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as LaravelEloquentModel;

abstract class BaseModel extends LaravelEloquentModel implements HasResourceKey
{
    use CanOwn;
    use HashedRouteBinding;
    use HasFactory;

    protected static function newFactory()
    {
        $factoryName = apiato()->factoryDiscovery()
            ->resolveFactoryName(static::class);

        if (is_string($factoryName)) {
            return $factoryName::new();
        }

        return null;
    }

    /**
     * Get the resource key to be used for the JSON response.
     */
    public function getResourceKey(): string
    {
        return $this->resourceKey ?? class_basename($this);
    }

    /**
     * Get the hashed key for the model or a specific field.
     *
     * Returns the hashed primary key by default.
     */
    public function getHashedKey(string|null $field = null): mixed
    {
        if (is_null($field)) {
            $field = $this->getKeyName();
        }

        $attribute = $this->getAttribute($field);

        if (is_null($attribute)) {
            return null;
        }

        if (config('apiato.hash-id')) {
            $value = hashids()->encode($attribute);

            if ('' === $value) {
                throw new \RuntimeException('Failed to encode the given value.');
            }

            return $value;
        }

        return $this->getAttribute($field);
    }
}
