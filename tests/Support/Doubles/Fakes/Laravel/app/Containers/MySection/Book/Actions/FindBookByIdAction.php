<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Actions;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Tasks\FindBookByIdTask;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\FindBookByIdRequest;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Actions\Action as ParentAction;

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
