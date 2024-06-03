<?php

namespace Apiato\Core\Macros\Request;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use League\Fractal\Manager;

class GetRequestedIncludes
{
    public function __invoke(): callable
    {
        return function (): array {
            /** @var Request $this */
            return app(Manager::class)->parseIncludes($this->get(Config::get('apiato.requests.params.include'), []))->getRequestedIncludes();
        };
    }
}
