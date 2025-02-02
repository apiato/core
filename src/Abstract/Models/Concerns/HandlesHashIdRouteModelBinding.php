<?php

namespace Apiato\Abstract\Models\Concerns;

use Illuminate\Support\Str;

trait HandlesHashIdRouteModelBinding
{
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->resolveRouteBindingQuery($this, $this->processHashId($value), $field)->first();
    }

    public function processHashId(mixed $value): mixed
    {
        if ($this->shouldProcessHashIdRouteBinding($value)) {
            return hashids()->tryDecode($value) ?? $value;
        }

        return $value;
    }

    public function shouldProcessHashIdRouteBinding(mixed $value): bool
    {
        return config('apiato.hash-id') && is_string($value);
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        return $this->resolveChildRouteBindingQuery($childType, $this->processHashId($value), $field)->first();
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
