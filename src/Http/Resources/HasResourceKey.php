<?php

declare(strict_types=1);

namespace Apiato\Http\Resources;

trait HasResourceKey
{
    public function getResourceKey(): string
    {
        return class_basename($this);
    }
}
