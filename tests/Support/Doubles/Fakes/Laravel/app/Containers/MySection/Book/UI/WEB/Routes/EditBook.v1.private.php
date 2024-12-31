<?php

use Illuminate\Support\Facades\Route;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\UpdateBookController;

Route::get('books/{id}/edit', [UpdateBookController::class, 'edit'])
    ->middleware(['auth:web']);
