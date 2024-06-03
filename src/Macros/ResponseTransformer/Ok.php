<?php

namespace Apiato\Core\Macros\ResponseTransformer;

use Apiato\Core\Abstracts\Transformers\Transformer;
use Illuminate\Http\JsonResponse;
use Apiato\Core\Services\ResponseTransformer;

class Ok {
    public function __invoke(): callable
    {
        return function (): JsonResponse {
            /** @var ResponseTransformer $this */
            if (is_null($this->getTransformer())) {
                $this->transformWith(Transformer::empty());
            }
            return $this->respond(200);
        };
    }
}
