<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Tasks;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Data\Repositories\BookRepository;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Events\BooksListed;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Tasks\Task as ParentTask;

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
