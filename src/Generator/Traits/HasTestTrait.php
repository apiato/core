<?php

namespace Apiato\Core\Generator\Traits;

trait HasTestTrait
{
    abstract protected function getTestPath(): string;

    abstract protected function getTestContent(): string;
}
