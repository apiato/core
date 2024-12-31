<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Tasks;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Data\Repositories\BookRepository;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Events\BookRequested;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Exceptions\ResourceNotFound;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Tasks\Task as ParentTask;

class FindBookByIdTask extends ParentTask
{
    public function __construct(
        private readonly BookRepository $repository,
    ) {
    }

    public function run($id): Book
    {
        try {
            $book = $this->repository->find($id);
            BookRequested::dispatch($book);

            return $book;
        } catch (\Exception) {
            throw ResourceNotFound::create();
        }
    }
}
