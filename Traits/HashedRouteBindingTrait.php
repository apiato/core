<?php

namespace Apiato\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

trait HashedRouteBindingTrait
{
    /**
     * Retrieve the child model query for a bound value.
     *
     * @param string $childType
     * @param mixed $value
     * @param string|null $field
     * @return Relation
     */
    protected function resolveChildRouteBindingQuery($childType, $value, $field)
    {
        if (method_exists($this, Str::plural(Str::camel($childType)))) {
            return parent::resolveChildRouteBindingQuery($childType, $value, $field);
        }

        $relationship = $this->{Str::camel($childType)}();

        $field = $field ?: $relationship->getRelated()->getRouteKeyName();

        return $relationship instanceof Model
            ? $relationship->resolveRouteBindingQuery($relationship, $value, $field)
            : $relationship->getRelated()->resolveRouteBindingQuery($relationship, $value, $field);
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param Model|Relation $query
     * @param mixed $value
     * @param string|null $field
     * @return Relation
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return $query->where($field ?? $this->getRouteKeyName(), config('apiato.hash-id') ? Hashids::decode($value)[0] : $value);
    }
}
