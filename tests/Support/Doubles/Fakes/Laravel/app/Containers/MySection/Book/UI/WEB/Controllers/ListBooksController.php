<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Actions\ListBooksAction;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Requests\ListBooksRequest;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Controllers\WebController;

class ListBooksController extends WebController
{
    public function index(ListBooksRequest $request)
    {
        $books = app(ListBooksAction::class)->run($request);
        // ...
    }
}
