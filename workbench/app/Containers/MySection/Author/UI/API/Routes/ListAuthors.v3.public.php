<?php

use Illuminate\Support\Facades\Route;

Route::get('authors', static function (): string {
    return 'List all Authors';
});
