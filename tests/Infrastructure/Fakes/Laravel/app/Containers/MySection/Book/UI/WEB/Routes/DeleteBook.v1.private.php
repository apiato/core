<?php

use Illuminate\Support\Facades\Route;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\DeleteBookController;

Route::delete('books/{id}', [DeleteBookController::class, 'destroy'])
    ->middleware(['auth:web']);
