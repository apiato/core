<?php

use Illuminate\Support\Facades\Route;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Controllers\UpdateBookController;

Route::patch('books/{id}', [UpdateBookController::class, 'update'])
    ->middleware(['auth:web']);
