<?php

use Illuminate\Support\Facades\Route;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\CreateBookController;

Route::post('books/store', [CreateBookController::class, 'store'])
    ->middleware(['auth:web']);
