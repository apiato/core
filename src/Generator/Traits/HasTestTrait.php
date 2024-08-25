<?php

namespace Apiato\Core\Generator\Traits;

trait HasTestTrait
{
    abstract public function getTestPath(): string;

    abstract public function getTestContent(): string;
}
