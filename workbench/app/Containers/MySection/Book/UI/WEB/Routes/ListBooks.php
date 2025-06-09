<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('books', static fn (): string => 'Get All Books');
