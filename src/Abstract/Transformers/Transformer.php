<?php

namespace Apiato\Abstract\Transformers;

use Apiato\Foundation\Exceptions\InternalError;
use Apiato\Foundation\Exceptions\UnsupportedInclude;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Primitive;
use League\Fractal\Scope;
use League\Fractal\TransformerAbstract as FractalTransformer;

abstract class Transformer extends FractalTransformer
{
    public function nullableItem($data, $transformer, string|null $resourceKey = null): Primitive|Item
    {
        if (is_null($data)) {
            return $this->primitive(null);
        }

        return $this->item($data, $transformer, $resourceKey);
    }

    public function item($data, $transformer, string|null $resourceKey = null): Item
    {
        // set a default resource key if none is set
        if (!$resourceKey && $data) {
            $resourceKey = $data->getResourceKey();
        }

        return parent::item($data, $transformer, $resourceKey);
    }

    public function collection($data, $transformer, string|null $resourceKey = null): Collection
    {
        // set a default resource key if none is set
        if (!$resourceKey && $data->isNotEmpty()) {
            $resourceKey = $data->first()->getResourceKey();
        }

        return parent::collection($data, $transformer, $resourceKey);
    }

    protected function callIncludeMethod(Scope $scope, string $includeName, $data)
    {
        try {
            return parent::callIncludeMethod($scope, $includeName, $data);
        } catch (\ErrorException $exception) {
            if (config('apiato.requests.force-valid-includes', true)) {
                throw new UnsupportedInclude($exception->getMessage());
            }
        } catch (\Exception $exception) {
            throw new InternalError($exception->getMessage());
        }

        return null;
    }

    public static function empty(): callable
    {
        return static fn (): array => [];
    }
}
