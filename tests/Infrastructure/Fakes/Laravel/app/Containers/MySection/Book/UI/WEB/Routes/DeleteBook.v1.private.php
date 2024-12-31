<?php

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\DeleteBookController;
use Illuminate\Support\Facades\Route;

Route::delete('books/{id}', [DeleteBookController::class, 'destroy'])
    ->middleware(['auth:web']);

