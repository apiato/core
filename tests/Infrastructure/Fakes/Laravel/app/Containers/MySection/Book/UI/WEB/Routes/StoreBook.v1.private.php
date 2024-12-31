<?php

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\CreateBookController;
use Illuminate\Support\Facades\Route;

Route::post('books/store', [CreateBookController::class, 'store'])
    ->middleware(['auth:web']);

