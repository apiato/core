<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Controllers;

use Apiato\Exceptions\CoreInternalErrorException;
use Apiato\Exceptions\InvalidTransformerException;
use Prettus\Repository\Exceptions\RepositoryException;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Actions\ListBooksAction;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\ListBooksRequest;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Transformers\BookTransformer;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Controllers\ApiController;

class ListBooksController extends ApiController
{
    /**
     * @throws InvalidTransformerException
     * @throws CoreInternalErrorException
     * @throws RepositoryException
     */
    public function __invoke(ListBooksRequest $request, ListBooksAction $action): array
    {
        $books = $action->run($request);

        return $this->transform($books, BookTransformer::class);
    }
}