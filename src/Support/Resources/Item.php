<?php

namespace Apiato\Support\Resources;

use Apiato\Contracts\Resource;
use League\Fractal\Resource\Item as FractalItem;

final class Item extends FractalItem
{
    public function getResourceKey(): string
    {
        if (!is_null($this->resourceKey)) {
            return $this->resourceKey;
        }

        if ($this->data instanceof Resource) {
            return $this->data->getResourceKey();
        }

        return '';
    }
}
