<?php

namespace Apiato\Core\Macros\Fractal;

use Apiato\Core\Services\Response;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;

class CreateFrom {
    public function __invoke(): callable
    {
        return function (mixed $data = null, callable|TransformerAbstract|null $transformer = null, SerializerAbstract|null $serializer = null): Response {
            return Response::create($data, $transformer, $serializer);
        };
    }
}
