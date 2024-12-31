<?php

use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\UpdateBookController;
use Illuminate\Support\Facades\Route;

Route::get('books/{id}/edit', [UpdateBookController::class, 'edit'])
    ->middleware(['auth:web']);

