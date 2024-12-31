<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Actions;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Tasks\DeleteBookTask;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\DeleteBookRequest;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Actions\Action as ParentAction;

class DeleteBookAction extends ParentAction
{
    public function __construct(
        private readonly DeleteBookTask $deleteBookTask,
    ) {
    }

    public function run(DeleteBookRequest $request): int
    {
        return $this->deleteBookTask->run($request->id);
    }
}
