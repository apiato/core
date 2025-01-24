<?php

use Illuminate\Support\Facades\Route;

Route::get('books', static fn(): string => 'Get All Books');
