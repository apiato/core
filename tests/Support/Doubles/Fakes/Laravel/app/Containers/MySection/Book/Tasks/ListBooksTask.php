<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Tasks;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Data\Repositories\BookRepository;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Events\BooksListed;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Tasks\Task as ParentTask;

class ListBooksTask extends ParentTask
{
    public function __construct(
        private readonly BookRepository $repository,
    ) {
    }

    public function run(): mixed
    {
        $result = $this->repository->addRequestCriteria()->paginate();
        BooksListed::dispatch($result);

        return $result;
    }
}
