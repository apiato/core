<?php

namespace Apiato\Core\Macros\Request;

use Illuminate\Http\Request;
use League\Fractal\Manager;

class GetRequestedIncludes
{
    public function __invoke(): callable
    {
        return function (): array {
            /** @var Request $this */
            return app(Manager::class)->parseIncludes($this->get(config('apiato.requests.params.fractal.include'), []))->getRequestedIncludes();
        };
    }
}
