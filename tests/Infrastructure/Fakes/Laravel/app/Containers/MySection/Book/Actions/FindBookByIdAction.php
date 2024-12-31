<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Actions;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Tasks\FindBookByIdTask;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\FindBookByIdRequest;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Actions\Action as ParentAction;

class FindBookByIdAction extends ParentAction
{
    public function __construct(
        private readonly FindBookByIdTask $findBookByIdTask,
    ) {
    }

    public function run(FindBookByIdRequest $request): Book
    {
        return $this->findBookByIdTask->run($request->id);
    }
}
