<?php

/**
 * @apiGroup           Book
 * @apiName            Invoke
 *
 * @api                {PATCH} /v1/books/:id Invoke
 * @apiDescription     Endpoint description here...
 *
 * @apiVersion         1.0.0
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

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Controllers\UpdateBookController;
use Illuminate\Support\Facades\Route;

Route::patch('books/{id}', UpdateBookController::class)
    ->middleware(['auth:api']);

