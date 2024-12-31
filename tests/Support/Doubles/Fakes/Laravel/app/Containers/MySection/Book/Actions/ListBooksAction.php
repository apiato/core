<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Actions;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Tasks\ListBooksTask;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\ListBooksRequest;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Actions\Action as ParentAction;

class ListBooksAction extends ParentAction
{
    public function __construct(
        private readonly ListBooksTask $listBooksTask,
    ) {
    }

    public function run(ListBooksRequest $request): mixed
    {
        return $this->listBooksTask->run();
    }
}
