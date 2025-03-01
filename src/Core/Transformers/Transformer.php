<?php

namespace Apiato\Core\Transformers;

use Apiato\Http\Resources\Collection;
use Apiato\Http\Resources\Item;
use League\Fractal\Resource\Primitive;
use League\Fractal\TransformerAbstract as FractalTransformer;

abstract class Transformer extends FractalTransformer
{
    public function nullableItem(mixed $data, callable|self $transformer, string|null $resourceKey = null): Primitive|Item
    {
        if (is_null($data)) {
            return $this->primitive(null);
        }

        return $this->item($data, $transformer, $resourceKey);
    }

    public function item($data, $transformer, string|null $resourceKey = null): Item
    {
        return new Item($data, $transformer, $resourceKey);
    }

    public function collection($data, $transformer, string|null $resourceKey = null): Collection
    {
        return new Collection($data, $transformer, $resourceKey);
    }

    public static function empty(): callable
    {
        return static fn (): array => [];
    }
}
