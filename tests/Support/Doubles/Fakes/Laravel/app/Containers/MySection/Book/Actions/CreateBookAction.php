<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Actions;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Tasks\CreateBookTask;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\CreateBookRequest;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Actions\Action as ParentAction;

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
