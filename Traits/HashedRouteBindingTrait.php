<?php

namespace Apiato\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Vinkla\Hashids\Facades\Hashids;

trait HashedRouteBindingTrait
{
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
