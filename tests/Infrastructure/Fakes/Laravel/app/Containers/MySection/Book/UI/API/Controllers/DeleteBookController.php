<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Controllers;

use Illuminate\Http\JsonResponse;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Actions\DeleteBookAction;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests\DeleteBookRequest;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Controllers\ApiController;

class DeleteBookController extends ApiController
{
    public function __invoke(DeleteBookRequest $request, DeleteBookAction $action): JsonResponse
    {
        $action->run($request);

        return $this->noContent();
    }
}
