<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Tasks;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Data\Repositories\BookRepository;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Events\BookUpdated;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Tasks\Task as ParentTask;

class UpdateBookTask extends ParentTask
{
    public function __construct(
        private readonly BookRepository $repository,
    ) {
    }

    public function run(array $data, $id): Book
    {
        $book = $this->repository->update($data, $id);
        BookUpdated::dispatch($book);

        return $book;
    }
}
