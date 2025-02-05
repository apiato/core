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
            'all' => $request->all(),
            'id' => $request->id,
            'validated' => $request->validated(),
        ]);
    }
}
