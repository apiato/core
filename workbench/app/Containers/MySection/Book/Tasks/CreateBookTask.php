<?php

namespace Workbench\App\Containers\MySection\Book\Tasks;

use Workbench\App\Containers\MySection\Book\Data\Repositories\BookRepository;
use Workbench\App\Containers\MySection\Book\Events\BookCreated;
use Workbench\App\Containers\MySection\Book\Models\Book;
use Workbench\App\Ship\Exceptions\CreateResourceFailed;
use Workbench\App\Ship\Parents\Tasks\Task as ParentTask;

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
