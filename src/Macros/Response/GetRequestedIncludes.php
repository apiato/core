<?php

namespace Apiato\Core\Macros\Response;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use League\Fractal\Manager;

class GetRequestedIncludes
{
    public function __invoke(): callable
    {
        return
            /**
             * Parse the include parameter from the request and return an array of resources to include.
             *
             * Includes can be Array or csv string of resources to include.
             */
            function (): array {
                return app(Manager::class)->parseIncludes(Request::get(Config::get('apiato.requests.params.include', 'include'), []))->getRequestedIncludes();
            };
    }
}
