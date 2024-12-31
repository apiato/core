<?php

use Illuminate\Support\Facades\Route;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\DeleteBookController;

Route::delete('books/{id}', [DeleteBookController::class, 'destroy'])
    ->middleware(['auth:web']);
