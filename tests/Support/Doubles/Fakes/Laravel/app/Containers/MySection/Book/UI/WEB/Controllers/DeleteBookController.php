<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Actions\DeleteBookAction;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Requests\DeleteBookRequest;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Controllers\WebController;

class DeleteBookController extends WebController
{
    public function destroy(DeleteBookRequest $request)
    {
        $result = app(DeleteBookAction::class)->run($request);
        // ...
    }
}
