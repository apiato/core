<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Tasks;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Data\Repositories\BookRepository;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Events\BookDeleted;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Tasks\Task as ParentTask;

class DeleteBookTask extends ParentTask
{
    public function __construct(
        private readonly BookRepository $repository,
    ) {
    }

    public function run($id): bool
    {
        $result = $this->repository->delete($id);
        BookDeleted::dispatch($result);

        return $result;
    }
}
