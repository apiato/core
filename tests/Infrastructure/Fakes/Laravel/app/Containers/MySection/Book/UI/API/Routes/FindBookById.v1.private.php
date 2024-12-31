<?php

/**
 * @apiGroup           Book
 *
 * @apiName            Invoke
 *
 * @api                {GET} /v1/books/:id Invoke
 *
 * @apiDescription     Endpoint description here...
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated ['permissions' => '', 'roles' => '']
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiParam           {String} parameters here...
 *
 * @apiSuccessExample  {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *     // Insert the response of the request here...
 * }
 */

use Illuminate\Support\Facades\Route;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Controllers\FindBookByIdController;

Route::get('books/{id}', FindBookByIdController::class)
    ->middleware(['auth:api']);
