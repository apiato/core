<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Tasks;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Data\Repositories\BookRepository;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Events\BookCreated;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Exceptions\CreateResourceFailed;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Tasks\Task as ParentTask;

class CreateBookTask extends ParentTask
{
    public function __construct(
        private readonly BookRepository $repository,
    ) {
    }

    public function run(array $data): Book
    {
        try {
            $book = $this->repository->create($data);
            BookCreated::dispatch($book);

            return $book;
        } catch (\Exception) {
            throw CreateResourceFailed::create();
        }
    }
}
