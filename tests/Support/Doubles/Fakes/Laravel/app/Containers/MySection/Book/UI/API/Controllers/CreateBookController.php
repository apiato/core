<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Controllers;

use Illuminate\Http\JsonResponse;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Actions\CreateBookAction;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\CreateBookRequest;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Transformers\BookTransformer;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Controllers\ApiController;

class CreateBookController extends ApiController
{
    public function __invoke(CreateBookRequest $request, CreateBookAction $action): JsonResponse
    {
        $book = $action->run($request);

        return $this->created($this->transform($book, BookTransformer::class));
    }
}