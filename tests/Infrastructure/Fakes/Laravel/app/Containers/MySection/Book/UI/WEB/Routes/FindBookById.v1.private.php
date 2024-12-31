<?php

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\FindBookByIdController;
use Illuminate\Support\Facades\Route;

Route::get('books/{id}', [FindBookByIdController::class, 'show'])
    ->middleware(['auth:web']);

