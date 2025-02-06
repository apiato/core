<?php

namespace Workbench\App\Containers\MySection\Book\UI\API\Controllers;

use Illuminate\Http\JsonResponse;
use Workbench\App\Containers\MySection\Book\UI\API\Requests\UpdateBookRequest;
use Workbench\App\Ship\Parents\Controllers\ApiController;

class UpdateBookController extends ApiController
{
    public function __invoke(UpdateBookRequest $request): JsonResponse
    {
        return $this->created([
            'input' => $request->input(),
            'input.id' => $request->input('id'),
            'input.title' => $request->input('title'),
            'input.nested.id' => $request->input('nested.id'),
            'input.author_id' => $request->input('author_id'),
            'input.none_existing' => $request->input('none_existing'),
            'input.optional_id' => $request->input('optional_id'),
            'all' => $request->all(),
            'all.id' => $request->all('id'),
            'all.title' => $request->all('title'),
            'all.nested.id' => $request->all('nested.id'),
            'all.author_id' => $request->all('author_id'),
            'all.none_existing' => $request->all('none_existing'),
            'all.optional_id' => $request->all('optional_id'),
            'route' => $request->route()::class,
            'route.id' => $request->route('id'),
            'route.none_existing' => $request->route('none_existing'),
            'request.id' => $request->id,
            'request.title' => $request->title,
            'request.none_existing' => $request->none_existing,
            'request.optional_id' => $request->optional_id,
            'validated' => $request->validated(),
        ]);
    }
}
