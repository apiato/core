<?php

use Illuminate\Support\Facades\Route;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\MySection\Book\Models\Book;

Route::get(
    'authors/{author}/children/{children:name}/books/{book:id}',
    static fn (User $author, User $children, Book $book): string => $book->author->name,
)->scopeBindings();
