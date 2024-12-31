<?php

use Illuminate\Support\Facades\Route;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\CreateBookController;

Route::get('books/create', [CreateBookController::class, 'create'])
    ->middleware(['auth:web']);
