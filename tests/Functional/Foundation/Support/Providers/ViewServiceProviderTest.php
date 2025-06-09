<?php

declare(strict_types=1);

use Apiato\Foundation\Support\Providers\ViewServiceProvider;
use Illuminate\Support\Facades\View;

describe(class_basename(ViewServiceProvider::class), function (): void {
    it('loads view files', function (): void {
        expect(View::exists('mySection@author::author'))->toBeTrue()
            ->and(View::exists('mySection@book::book-me'))->toBeTrue()
            ->and(View::exists('ship::welcome-cheers'))->toBeTrue()
            ->and(View::exists('ship::something'))->toBeTrue();
    });
})->covers(ViewServiceProvider::class);
