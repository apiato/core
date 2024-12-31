<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Controllers;

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Actions\CreateBookAction;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\CreateBookRequest;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Transformers\BookTransformer;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class CreateBookController extends ApiController
{
    public function __invoke(CreateBookRequest $request, CreateBookAction $action): JsonResponse
    {
        $book = $action->run($request);

        return $this->created($this->transform($book, BookTransformer::class));
    }
}
