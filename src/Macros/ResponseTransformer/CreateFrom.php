<?php

namespace Apiato\Core\Macros\ResponseTransformer;

use Apiato\Core\Services\ResponseTransformer;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;

class CreateFrom {
    public function __invoke(): callable
    {
        return
            /**
             * Create a new Response instance.
             */
            function (mixed $data = null, callable|TransformerAbstract|null $transformer = null, SerializerAbstract|null $serializer = null): ResponseTransformer {
                return ResponseTransformer::create($data, $transformer, $serializer);
            };
    }
}
