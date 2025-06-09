<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Workbench\App\Containers\MySection\Book\UI\WEB\Controllers\CreateBookController;

Route::get('books/create', [CreateBookController::class, 'create']);
