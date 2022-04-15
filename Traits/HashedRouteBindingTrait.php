<?php

namespace Apiato\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

trait HashedRouteBindingTrait
{
    /**
     * @param $value
     * @param $field
     * @return Model|null
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->where('id', config('apiato.hash-id') ? Hashids::decode($value) : $value)->firstOrFail();
    }
}
