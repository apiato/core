<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Actions\FindBookByIdAction;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Requests\FindBookByIdRequest;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Controllers\WebController;

class FindBookByIdController extends WebController
{
    public function show(FindBookByIdRequest $request)
    {
        $book = app(FindBookByIdAction::class)->run($request);
        // ...
    }
}
