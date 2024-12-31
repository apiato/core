<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Actions\DeleteBookAction;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Requests\DeleteBookRequest;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Controllers\WebController;

class DeleteBookController extends WebController
{
    public function destroy(DeleteBookRequest $request)
    {
        $result = app(DeleteBookAction::class)->run($request);
        // ...
    }
}
