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
            'input(val)' => [
                'id' => $request->input('id'),
                'id-default' => $request->input('id', 100),
                'title' => $request->input('title'),
                'hashed_id' => $request->input('hashed_id'),
                'nested.id' => $request->input('nested.id'),
                'nested.with-default' => $request->input('nested.with', 200),
                'author_id' => $request->input('author_id'),
                'authors' => $request->input('authors'),
                'authors.*.id' => $request->input('authors.*.id'),
                'authors.*.with-default' => $request->input('authors.*.with', 150),
                'ids' => $request->input('ids'),
                'with-default' => $request->input('with', [1, 2, 3]),
                'none_existing' => $request->input('none_existing'),
                'optional_id' => $request->input('optional_id'),
            ],
            'all(val)' => [
                'id' => $request->all('id'),
                'title' => $request->all('title'),
                'nested.id' => $request->all('nested.id'),
                'nested.ids' => $request->all('nested.ids'),
                'author_id' => $request->all('author_id'),
                'none_existing' => $request->all('none_existing'),
                'optional_id' => $request->all('optional_id'),
            ],
            'route(val)' => [
                'id' => $request->route('id'),
                'none_existing' => $request->route('none_existing'),
            ],
            'request->val' => [
                'id' => $request->id,
                'title' => $request->title,
                'none_existing' => $request->none_existing,
                'optional_id' => $request->optional_id,
            ],
            'input()' => $request->input(),
            'all()' => $request->all(),
            'validated' => $request->validated(),
            'route()::class' => $request->route()::class,
        ]);
    }
}
