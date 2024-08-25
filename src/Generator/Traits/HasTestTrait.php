<?php

namespace Apiato\Core\Generator\Traits;

trait HasTestTrait
{
    abstract function getTestPath(): string;

    abstract function getTestContent(): string;
}
