<?php

namespace Workbench\App\Containers\MySection\Book\UI\WEB\Controllers;

use Workbench\App\Containers\MySection\Book\Actions\CreateBookAction;
use Workbench\App\Containers\MySection\Book\UI\WEB\Requests\CreateBookRequest;
use Workbench\App\Containers\MySection\Book\UI\WEB\Requests\StoreBookRequest;
use Workbench\App\Ship\Parents\Controllers\WebController;

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
