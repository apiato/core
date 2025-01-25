<?php

namespace Apiato\Contracts;

interface Resource
{
    /**
     * Get the resource key to be used for the response serialization.
     */
    public function getResourceKey(): string;
}
