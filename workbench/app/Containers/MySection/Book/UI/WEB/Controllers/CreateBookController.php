<?php

declare(strict_types=1);

namespace Workbench\App\Containers\MySection\Book\UI\WEB\Controllers;

use Illuminate\View\View;
use Workbench\App\Containers\MySection\Book\UI\WEB\Requests\CreateBookRequest;
use Workbench\App\Ship\Parents\Controllers\WebController;

class CreateBookController extends WebController
{
    public function create(CreateBookRequest $request): View
    {
        return view('placeholder');
    }
}
