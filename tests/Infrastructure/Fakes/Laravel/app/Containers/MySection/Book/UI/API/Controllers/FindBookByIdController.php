<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Controllers;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Actions\FindBookByIdAction;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\FindBookByIdRequest;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Transformers\BookTransformer;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Controllers\ApiController;

class FindBookByIdController extends ApiController
{
    public function __invoke(FindBookByIdRequest $request, FindBookByIdAction $action): array
    {
        $book = $action->run($request);

        return $this->transform($book, BookTransformer::class);
    }
}
