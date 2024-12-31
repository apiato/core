<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Actions;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Tasks\UpdateBookTask;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\UpdateBookRequest;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Actions\Action as ParentAction;

class UpdateBookAction extends ParentAction
{
    public function __construct(
        private readonly UpdateBookTask $updateBookTask,
    ) {
    }

    public function run(UpdateBookRequest $request): Book
    {
        $data = $request->sanitizeInput([
            // add your request data here
        ]);

        return $this->updateBookTask->run($data, $request->id);
    }
}
