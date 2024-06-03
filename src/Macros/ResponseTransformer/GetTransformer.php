<?php

namespace Apiato\Core\Macros\ResponseTransformer;

use Apiato\Core\Services\ResponseTransformer;
use League\Fractal\TransformerAbstract;

class GetTransformer
{
    public function __invoke(): callable
    {
        return function (): string|callable|TransformerAbstract|null {
            /** @var ResponseTransformer $this */
            // The warning is false positive. We will be in the context of Fractal class when this is called.
            return $this->transformer;
        };
    }
}
