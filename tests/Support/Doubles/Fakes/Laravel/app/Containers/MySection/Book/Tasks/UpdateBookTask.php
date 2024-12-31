<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Tasks;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Data\Repositories\BookRepository;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Events\BookUpdated;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Tasks\Task as ParentTask;

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
