<?php

namespace Apiato\Core\Traits;

use Apiato\Core\Exceptions\IncorrectIdException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

trait HashedRouteBindingTrait
{
    /**
     * Retrieve the model for a bound value.
     *
     * @param Model|Relation $query
     * @param mixed $value
     * @param string|null $field
     *
     * @throws IncorrectIdException
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if (config('apiato.hash-id')) {
            $decodingResult = $this->decode($value);
            if (is_null($decodingResult)) {
                throw new IncorrectIdException();
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
