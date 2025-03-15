<?php

use Apiato\Macros\MacroServiceProvider;

describe(class_basename(MacroServiceProvider::class), function (): void {
    describe('hashids interactions', function (): void {
        it('can search for decoded value', function (): void {
            $hashedId = hashids()->encodeOrFail(20);
            $collection = collect([
                ['id' => 10],
                ['id' => 20],
                ['id' => 30],
            ]);

            expect($collection->containsDecodedHash($hashedId))->toBeTrue();
        });

        it('can decode all hashed values', function (): void {
            $collection = collect([
                hashids()->encode(10),
                hashids()->encode(20),
                hashids()->encode(30),
            ]);

            $decoded = $collection->decode();

            expect($decoded->toArray())->toBe([10, 20, 30]);
        });

        it('throws an exception if a value is not decodable', function (): void {
            $collection = collect([
                hashids()->encode(10),
                'not-a-hashid',
                hashids()->encode(30),
            ]);

            expect(static function () use ($collection) {
                return $collection->decode();
            })->toThrow(InvalidArgumentException::class);
        });
    });
})->covers(MacroServiceProvider::class);
