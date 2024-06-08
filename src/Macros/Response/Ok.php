<?php

namespace Apiato\Core\Macros\Response;

use Apiato\Core\Abstracts\Transformers\Transformer;
use Illuminate\Http\JsonResponse;
use Apiato\Core\Services\Response;

class Ok {
    public function __invoke(): callable
    {
        return
            /**
             * Returns a 200 OK response.
             */
            function (): JsonResponse {
                /** @var Response $this */
                if (is_null($this->getTransformer())) {
                    $this->transformWith(Transformer::empty());
                }
                return $this->respond(200);
            };
    }
}
