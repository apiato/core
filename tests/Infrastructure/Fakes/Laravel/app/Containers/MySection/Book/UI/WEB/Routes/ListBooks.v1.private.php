<?php

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\ListBooksController;
use Illuminate\Support\Facades\Route;

Route::get('books', [ListBooksController::class, 'index'])
    ->middleware(['auth:web']);

