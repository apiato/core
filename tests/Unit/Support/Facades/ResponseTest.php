<?php

use Apiato\Support\Facades\Response;

describe(class_basename(Response::class), function (): void {
    it('should have the correct facade accessor', function (): void {
        expect(Response::create())
            ->toBeInstanceOf(Apiato\Http\Response::class);
    });
})->covers(Response::class);
