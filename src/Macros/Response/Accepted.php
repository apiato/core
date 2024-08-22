<?php

namespace Apiato\Core\Macros\Response;

use Apiato\Core\Abstracts\Transformers\Transformer;
use Apiato\Core\Services\Response;
use Illuminate\Http\JsonResponse;

class Accepted
{
    public function __invoke(): callable
    {
        return
            /**
             * Returns a 202 Accepted response.
             */
            function (): JsonResponse {
                /** @var Response $this */
                if (is_null($this->getTransformer())) {
                    $this->transformWith(Transformer::empty());
                }

                return $this->respond(202);
            };
    }
}
