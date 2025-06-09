<?php

declare(strict_types=1);

namespace Workbench\App\Containers\MySection\Book\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use Workbench\App\Containers\MySection\Book\Actions\CreateBookAction;
use Workbench\App\Containers\MySection\Book\UI\API\Requests\CreateBookRequest;
use Workbench\App\Containers\MySection\Book\UI\API\Transformers\BookTransformer;
use Workbench\App\Ship\Parents\Controllers\ApiController;

class CreateBookController extends ApiController
{
    public function __invoke(CreateBookRequest $request, CreateBookAction $action): JsonResponse
    {
        $book = $action->run($request);

        return Response::create($book, BookTransformer::class)->created();
    }
}
