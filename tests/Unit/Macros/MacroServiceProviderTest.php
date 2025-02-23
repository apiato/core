<?php

use Apiato\Macros\MacroServiceProvider;

describe(class_basename(MacroServiceProvider::class), function (): void {
    it('can search for decoded value', function (): void {
        $hashedId = hashids()->encodeOrFail(20);
        $collection = collect([
            ['id' => 10],
            ['id' => 20],
            ['id' => 30],
        ]);

        expect($collection->containsDecodedHash($hashedId))->toBeTrue();
    });
})->covers(MacroServiceProvider::class);
