<?php

namespace Apiato\Abstract\Models;

use Apiato\Contracts\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as LaravelEloquentModel;
use Illuminate\Support\Str;

abstract class BaseModel extends LaravelEloquentModel implements Resource
{
    use HasFactory;

    protected static function newFactory()
    {
        $factoryName = apiato()->factory()
            ->resolveFactoryName(static::class);

        if (is_string($factoryName)) {
            return $factoryName::new();
        }

        return null;
    }

    public function getResourceKey(): string
    {
        if (property_exists($this, 'resourceKey') && is_string($this->resourceKey)) {
            return $this->resourceKey;
        }

        return class_basename($this);
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

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if ($this->shouldResolveRouteBindingAsHashedId($value)) {
            $value = hashids()->decode($value)[0];
        }

        return $query->where($field ?? $this->getRouteKeyName(), $value);
    }

    public function shouldResolveRouteBindingAsHashedId(mixed $value): bool
    {
        return config('apiato.hash-id') && is_string($value) && [] !== hashids()->decode($value);
    }

    protected function childRouteBindingRelationshipName($childType): string
    {
        $relationship = Str::camel($childType);
        if (!method_exists($this, $relationship)) {
            $relationship = Str::plural($relationship);
        }

        return $relationship;
    }
}
