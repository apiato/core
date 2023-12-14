<?php

namespace Apiato\Core\Traits;

use ReflectionClass;

trait HasResourceKeyTrait
{
    /**
     * Returns the type for JSON API Serializer. Can be overwritten with the protected $resourceKey in respective model class.
     */
    public function getResourceKey(): string
    {
        if (isset($this->resourceKey)) {
            $resourceKey = $this->resourceKey;
        } else {
            $reflect = new ReflectionClass($this);
            $resourceKey = $reflect->getShortName();
        }

        return $resourceKey;
    }
}
