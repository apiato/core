<?php

use Illuminate\Support\Facades\Route;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\CreateBookController;

Route::post('books/store', [CreateBookController::class, 'store'])
    ->middleware(['auth:web']);
