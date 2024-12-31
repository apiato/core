<?php

use Illuminate\Support\Facades\Route;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\FindBookByIdController;

Route::get('books/{id}', [FindBookByIdController::class, 'show'])
    ->middleware(['auth:web']);
