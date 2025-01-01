<?php

use Illuminate\Support\Facades\Route;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Controllers\CreateBookController;

Route::post('books', CreateBookController::class);
