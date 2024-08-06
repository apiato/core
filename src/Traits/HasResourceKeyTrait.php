<?php

namespace Apiato\Core\Traits;

use JetBrains\PhpStorm\Deprecated;

trait HasResourceKeyTrait
{
    #[Deprecated(reason: 'Override the getResourceKey method in the respective model class instead of using the $resourceKey property.')]
    protected string $resourceKey;

    /**
     * Returns the type for JSON API Serializer. Can be overwritten with the protected $resourceKey in respective model class.
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
