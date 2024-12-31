<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Actions;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Tasks\ListBooksTask;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\ListBooksRequest;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Actions\Action as ParentAction;

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
