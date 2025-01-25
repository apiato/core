<?php

namespace Apiato\Contracts;

interface Resource
{
    /**
     * Get the resource key to be used for the JSON response.
     */
    public function getResourceKey(): string;
}
