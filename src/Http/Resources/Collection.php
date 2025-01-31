<?php

namespace Apiato\Http\Resources;

use Apiato\Contracts\Resource;
use League\Fractal\Resource\Collection as FractalCollection;

final class Collection extends FractalCollection
{
    public function getResourceKey(): string
    {
        if (!is_null($this->resourceKey)) {
            return $this->resourceKey;
        }

        $resource = $this->data;

        if (is_array($resource)) {
            $resource = reset($resource);
        }
        if ($resource instanceof \IteratorAggregate) {
            $resource = $resource->getIterator();
        }
        if ($resource instanceof \Iterator) {
            $resource = $resource->current();
        }

        if ($resource instanceof Resource) {
            return $resource->getResourceKey();
        }

        return '';
    }
}
