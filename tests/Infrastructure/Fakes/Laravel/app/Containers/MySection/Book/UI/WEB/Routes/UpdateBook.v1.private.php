<?php

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\UpdateBookController;
use Illuminate\Support\Facades\Route;

Route::patch('books/{id}', [UpdateBookController::class, 'update'])
    ->middleware(['auth:web']);

