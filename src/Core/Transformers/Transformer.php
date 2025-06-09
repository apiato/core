<?php

declare(strict_types=1);

namespace Apiato\Core\Transformers;

use Apiato\Http\Resources\Collection;
use Apiato\Http\Resources\Item;
use League\Fractal\Resource\Primitive;
use League\Fractal\TransformerAbstract as FractalTransformer;

abstract class Transformer extends FractalTransformer
{
    public static function empty(): callable
    {
        return static fn (): array => [];
    }

    public function nullableItem(mixed $data, callable|self $transformer, null|string $resourceKey = null): Primitive|Item
    {
        if (\is_null($data)) {
            return $this->primitive(null);
        }

        return $this->item($data, $transformer, $resourceKey);
    }

    /** @inheritDoc */
    #[\Override]
    public function item($data, $transformer, null|string $resourceKey = null): Item
    {
        return new Item($data, $transformer, $resourceKey);
    }

    /** @inheritDoc */
    #[\Override]
    public function collection($data, $transformer, null|string $resourceKey = null): Collection
    {
        return new Collection($data, $transformer, $resourceKey);
    }
}
