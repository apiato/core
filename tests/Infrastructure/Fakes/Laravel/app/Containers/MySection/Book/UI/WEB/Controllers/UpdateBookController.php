<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Actions\FindBookByIdAction;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Actions\UpdateBookAction;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Requests\EditBookRequest;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Requests\UpdateBookRequest;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Controllers\WebController;

class UpdateBookController extends WebController
{
    public function edit(EditBookRequest $request)
    {
        $book = app(FindBookByIdAction::class)->run($request);
        // ...
    }

    public function update(UpdateBookRequest $request)
    {
        $book = app(UpdateBookAction::class)->run($request);
        // ...
    }
}
