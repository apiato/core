<?php

namespace Apiato\Core\Macros\ResponseTransformer;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use League\Fractal\Manager;

class GetRequestedIncludes
{
    public function __invoke(): callable
    {
        return function (): array {
            return app(Manager::class)->parseIncludes(Request::get(Config::get('apiato.requests.params.include', 'include'), []))->getRequestedIncludes();
        };
    }
}
