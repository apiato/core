<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Controllers;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Actions\FindBookByIdAction;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\FindBookByIdRequest;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Transformers\BookTransformer;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Controllers\ApiController;

class FindBookByIdController extends ApiController
{
    public function __invoke(FindBookByIdRequest $request, FindBookByIdAction $action): array
    {
        $book = $action->run($request);

        return $this->transform($book, BookTransformer::class);
    }
}
