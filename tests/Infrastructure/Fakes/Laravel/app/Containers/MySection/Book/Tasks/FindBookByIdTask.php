<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Tasks;

use Tests\Infrastructure\Fakes\Laravel\app\Ship\Exceptions\ResourceNotFound;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Tasks\Task as ParentTask;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Data\Repositories\BookRepository;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Events\BookRequested;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;

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
