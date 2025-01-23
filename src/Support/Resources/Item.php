<?php

namespace Apiato\Support\Resources;

use Apiato\Contracts\HasResourceKey;
use League\Fractal\Resource\Item as FractalItem;

final class Item extends FractalItem
{
    public function getResourceKey(): string
    {
        if (!is_null($this->resourceKey)) {
            return $this->resourceKey;
        }

        if ($this->data instanceof HasResourceKey) {
            return $this->data->getResourceKey();
        }

        return '';
    }
}
