<?php

use Illuminate\Support\Facades\Route;
use Workbench\App\Containers\MySection\Book\UI\API\Controllers\CreateBookController;
use Workbench\App\Containers\MySection\Book\UI\API\Controllers\UpdateBookController;

Route::patch('books/{id}', UpdateBookController::class);
