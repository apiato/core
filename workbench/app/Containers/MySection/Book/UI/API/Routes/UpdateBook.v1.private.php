<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Workbench\App\Containers\MySection\Book\UI\API\Controllers\UpdateBookController;

Route::patch('books/{id}', UpdateBookController::class);
