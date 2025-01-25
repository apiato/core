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
     * Checks if the model is owned by the $owner model.
     *
     * It tries to guess the relation by the name of the $owner model.
     * e.g., if the $owner model is called User, then the first guess would be `user()`.
     * If the `user()` relation does not exist, then it tries to use the plural version `users()`.
     * Else it throws an exception.
     *
     * If the relation name is different, you can pass it as the second parameter.
     */
    public function isOwnedBy(Model $owner, string|null $relation = null): bool
    {
        return $this->owns($owner, $relation);
    }

    /**
     * Checks if the model is the owner of the $ownable model.
     *
     * It tries to guess the relation by the name of the $ownable model.
     * e.g. if the $ownable model is called Post, then the first guess would be `post()`.
     * If the `post()` relation does not exist, then it tries to use the plural version `posts()`.
     * Else it throws an exception.
     *
     * If the relation name is different, you can pass it as the second parameter.
     */
    public function owns(Model $ownable, string|null $relation = null): bool
    {
        if ($relation) {
            return !is_null($this->$relation()->find($ownable));
        }

        $relation = $this->guessSingularRelationshipName($ownable);
        if (method_exists($this, $relation)) {
            return !is_null($this->$relation()->find($ownable));
        }

        $relation = $this->guessPluralRelationshipName($ownable);
        if (method_exists($this, $relation)) {
            return !is_null($this->$relation()->find($ownable));
        }

        throw new \InvalidArgumentException('No relationship found. Please pass the relationship name as the second parameter.');
    }

    public function guessSingularRelationshipName(Model $ownable): string
    {
        return Str::camel(class_basename($ownable));
    }

    public function guessPluralRelationshipName(Model $ownable): string
    {
        return Str::plural(Str::camel(class_basename($ownable)));
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
