<?php

declare(strict_types=1);

namespace Apiato\Http\Resources;

use League\Fractal\Resource\Item as FractalItem;

final class Item extends FractalItem implements ResourceKeyAware
{
    public function getResourceKey(): string
    {
        if (!\is_null($this->resourceKey)) {
            return $this->resourceKey;
        }

        if ($this->data instanceof ResourceKeyAware) {
            return $this->data->getResourceKey();
        }

        return '';
    }
}
