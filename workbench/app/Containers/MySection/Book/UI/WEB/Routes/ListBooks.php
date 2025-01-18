<?php

use Illuminate\Support\Facades\Route;

Route::get('books', static function (): string {
    return 'Get All Books';
});
