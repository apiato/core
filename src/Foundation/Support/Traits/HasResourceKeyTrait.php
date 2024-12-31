<?php

namespace Apiato\Foundation\Support\Traits;

trait HasResourceKeyTrait
{
    /**
     * Returns the type for JSON API Serializer.
     *
     * If the $resourceKey property is set, it will be used as the resource key.
     * Otherwise, the class name will be used.
     */
    public function getResourceKey(): string
    {
        if (isset($this->resourceKey)) {
            $resourceKey = $this->resourceKey;
        } else {
            $reflect = new \ReflectionClass($this);
            $resourceKey = $reflect->getShortName();
        }

        return $resourceKey;
    }
}
