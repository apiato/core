<?php

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\CreateBookController;
use Illuminate\Support\Facades\Route;

Route::get('books/create', [CreateBookController::class, 'create'])
    ->middleware(['auth:web']);

