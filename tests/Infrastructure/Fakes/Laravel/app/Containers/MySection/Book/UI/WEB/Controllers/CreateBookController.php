<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Actions\CreateBookAction;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Requests\CreateBookRequest;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Requests\StoreBookRequest;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Controllers\WebController;

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
