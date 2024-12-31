<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Actions\CreateBookAction;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Requests\CreateBookRequest;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Requests\StoreBookRequest;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Controllers\WebController;

class CreateBookController extends WebController
{
    public function create(CreateBookRequest $request)
    {
    }

    public function store(StoreBookRequest $request)
    {
        $book = app(CreateBookAction::class)->run($request);
        // ...
    }
}
