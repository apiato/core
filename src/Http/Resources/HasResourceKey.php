<?php

namespace Apiato\Http\Resources;

trait HasResourceKey
{
    public function getResourceKey(): string
    {
        if (property_exists($this, 'resourceKey') && is_string($this->resourceKey)) {
            return $this->resourceKey;
        }

        return class_basename($this);
    }
}
