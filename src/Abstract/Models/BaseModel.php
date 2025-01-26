<?php

namespace Apiato\Abstract\Models;

use Apiato\Contracts\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as LaravelEloquentModel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

abstract class BaseModel extends LaravelEloquentModel implements Resource
{
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

    /**
     * Retrieve the model for a bound value.
     *
     * @param Model|Relation $query
     * @param string|null $field
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if (config('apiato.hash-id')) {
            $decodingResult = $this->decode($value);
            if (is_null($decodingResult)) {
                throw new \RuntimeException('Invalid ID');
            }
            $value = $decodingResult;
        }

        return $query->where($field ?? $this->getRouteKeyName(), $value);
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
