<?php

namespace Apiato\Foundation\Support\Traits;

use Apiato\Foundation\Exceptions\InternalError;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait CanOwn
{
    /**
     * Checks if the model is owned by the $owner model.
     *
     * It tries to guess the relation by the name of the $owner model.
     * e.g., if the $owner model is called User, then the first guess would be `user()`.
     * If the `user()` relation does not exist, then it tries to use the plural version `users()`.
     * Else it throws an exception.
     *
     * If the relation name is different, you can pass it as the second parameter.
     *
     * @throws InternalError
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
     *
     * @throws InternalError
     */
    public function owns(Model $ownable, string|null $relation = null): bool
    {
        if ($relation) {
            return null !== $this->$relation()->find($ownable);
        }

        $relation = $this->guessSingularRelationshipName($ownable);
        if (method_exists($this, $relation)) {
            return null !== $this->$relation()->find($ownable);
        }

        $relation = $this->guessPluralRelationshipName($ownable);
        if (method_exists($this, $relation)) {
            return null !== $this->$relation()->find($ownable);
        }

        throw new InternalError('No relationship found. Please pass the relationship name as the second parameter.');
    }

    protected function guessSingularRelationshipName(Model $ownable): string
    {
        return Str::camel(class_basename($ownable));
    }

    protected function guessPluralRelationshipName(Model $ownable): string
    {
        return Str::plural(Str::camel(class_basename($ownable)));
    }
}
