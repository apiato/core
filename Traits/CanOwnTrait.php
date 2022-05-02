<?php

namespace Apiato\Core\Traits;

use Apiato\Core\Exceptions\CoreInternalErrorException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

trait CanOwnTrait
{
    /**
     * check if the model is the owner of the $ownable model
     *
     * @param Model $ownable
     * @param null $foreignKeyName
     * @param null $localKey
     * @return bool
     * @throws Throwable
     */
    public function owns(Model $ownable, $foreignKeyName = null, $localKey = null): bool
    {
        $foreignKeyName = $foreignKeyName ?: $this->guessForeignKeyName();

        $ownerKey = $ownable->$foreignKeyName;

        throw_if(is_null($ownerKey), (new CoreInternalErrorException())->withErrors(['foreign_key' => 'No foreign key found.']));

        return $ownerKey == ($localKey ?? $this->getKey());
    }

    private function guessForeignKeyName(): string
    {
        $className = Str::snake(class_basename($this));

        return $className . '_id';
    }
}
