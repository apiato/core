<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Controllers;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Actions\UpdateBookAction;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\UpdateBookRequest;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Transformers\BookTransformer;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Controllers\ApiController;

class UpdateBookController extends ApiController
{
    public function __invoke(UpdateBookRequest $request, UpdateBookAction $action): array
    {
        $book = $action->run($request);

        return $this->transform($book, BookTransformer::class);
    }
}