<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Actions;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Tasks\CreateBookTask;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\CreateBookRequest;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Actions\Action as ParentAction;

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
