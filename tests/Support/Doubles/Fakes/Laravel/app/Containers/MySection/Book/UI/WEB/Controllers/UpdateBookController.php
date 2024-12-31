<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Actions\FindBookByIdAction;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Actions\UpdateBookAction;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Requests\EditBookRequest;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Requests\UpdateBookRequest;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Controllers\WebController;

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
