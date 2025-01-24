<?php

namespace Workbench\App\Containers\MySection\Book\Actions;

use Workbench\App\Containers\MySection\Book\Models\Book;
use Workbench\App\Containers\MySection\Book\Tasks\CreateBookTask;
use Workbench\App\Containers\MySection\Book\UI\API\Requests\CreateBookRequest;
use Workbench\App\Ship\Parents\Actions\Action as ParentAction;

class CreateBookAction extends ParentAction
{
    public function __construct(
        private readonly CreateBookTask $createBookTask,
    ) {
    }

    public function run(CreateBookRequest $request): Book
    {
        $data = $request->sanitizeInput([
            // add your request data here
        ]);

        return $this->createBookTask->run($data);
    }
}
