<?php

use Illuminate\Support\Facades\Route;

Route::get('authors', static fn(): string => 'List all Authors');
