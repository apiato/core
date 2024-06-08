<?php

namespace Apiato\Core\Macros\ResponseTransformer;

use Apiato\Core\Abstracts\Transformers\Transformer;
use Illuminate\Http\JsonResponse;
use Apiato\Core\Services\ResponseTransformer;

class Created {
    public function __invoke(): callable
    {
        return
            /**
             * Returns a 201 Created response.
             */
            function (): JsonResponse {
                /** @var ResponseTransformer $this */
                if (is_null($this->getTransformer())) {
                    $this->transformWith(Transformer::empty());
                }
                return $this->respond(201);
            };
    }
}
