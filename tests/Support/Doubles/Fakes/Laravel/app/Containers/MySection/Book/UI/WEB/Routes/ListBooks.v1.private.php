<?php

use Illuminate\Support\Facades\Route;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\ListBooksController;

Route::get('books', [ListBooksController::class, 'index'])
    ->middleware(['auth:web']);
