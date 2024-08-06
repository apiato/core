<?php

namespace Apiato\Core\Abstracts\Transformers;

use Apiato\Core\Contracts\HasResourceKey;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Primitive;
use League\Fractal\TransformerAbstract as FractalTransformer;

abstract class Transformer extends FractalTransformer
{
    protected function nullableItem($data, $transformer, $resourceKey = null): Primitive|Item
    {
        if (is_null($data)) {
            return $this->primitive(null);
        }

        return $this->item($data, $transformer, $resourceKey);
    }

    protected function item($data, $transformer, string|null $resourceKey = null): Item
    {
        if (!$resourceKey && $data instanceof HasResourceKey) {
            $resourceKey = $data->getResourceKey();
        }

        return parent::item($data, $transformer, $resourceKey);
    }

    protected function collection($data, $transformer, string|null $resourceKey = null): Collection
    {
        if (!$resourceKey && $data->isNotEmpty()) {
            $firstItem = $data->first();
            if ($firstItem instanceof HasResourceKey) {
                $resourceKey = $firstItem->getResourceKey();
            }
        }

        return parent::collection($data, $transformer, $resourceKey);
    }

    public static function empty(): callable
    {
        return static function () {
            return [];
        };
    }
}
