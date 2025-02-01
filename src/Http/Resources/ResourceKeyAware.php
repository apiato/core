<?php

namespace Apiato\Http\Resources;

interface ResourceKeyAware
{
    /**
     * Get the resource key to be used for the response serialization.
     */
    public function getResourceKey(): string;
}
