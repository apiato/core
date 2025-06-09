<?php

declare(strict_types=1);

use Apiato\Macros\MacroServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

describe(class_basename(MacroServiceProvider::class), function (): void {
    it('register Collection macros', function (): void {
        expect(Collection::hasMacro('containsDecodedHash'))->toBeTrue()
            ->and(Collection::hasMacro('decode'))->toBeTrue();
    });

    it('register Config macros', function (): void {
        expect(Config::hasMacro('unset'))->toBeTrue();
    });

    it('register Request macros', function (): void {
        expect(Request::hasMacro('sanitize'))->toBeTrue();
    });
})->covers(MacroServiceProvider::class);
