<?php

use Apiato\Support\HashidsManagerDecorator;
use Hashids\Hashids;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use Pest\Expectation;
use Vinkla\Hashids\HashidsManager;

describe(class_basename(HashidsManagerDecorator::class), function (): void {
    it('should not extend anything', function (): void {
        expect(class_parents(HashidsManagerDecorator::class))->toBe([]);
    });

    it('should use the ForwardsCalls trait', function (): void {
        expect(in_array(ForwardsCalls::class, class_uses(HashidsManagerDecorator::class)))->toBeTrue();
    });

    it('should use the Macroable trait', function (): void {
        expect(in_array(Macroable::class, class_uses(HashidsManagerDecorator::class)))->toBeTrue();
    });

    it('can decode or null', function (string $hashId, int|array|null $expectation): void {
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        $result = $sut->decode($hashId);

        expect($result)->toBe($expectation);
    })->with([
        [fn () => hashids()->encodeOrFail(10), 10],
        [fn () => hashids()->encodeOrFail(10, 13, 2), [10, 13, 2]],
        ['invalid', null],
    ]);

    it('can decode or throw an exception', function (array $hashId, int|array|null $expectation): void {
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        when(
            is_null($expectation),
            fn () => expect(static function () use ($sut, $hashId) {
                $sut->decodeOrFail(...$hashId);
            })->toThrow(InvalidArgumentException::class),
        );

        when(
            !is_null($expectation),
            fn () => expect($sut->decodeOrFail(...$hashId))->toBe($expectation),
        );
    })->with([
        [
            fn () => [hashids()->encodeOrFail(10)],
            10,
        ],
        [
            fn () => [hashids()->encodeOrFail(10, 13, 2)],
            [10, 13, 2],
        ],
        [
            fn () => [hashids()->encodeOrFail(10), 'invalid'],
            null,
        ],
        [
            fn () => [hashids()->encodeOrFail(10), ''],
            null,
        ],
        [
            ['invalid'],
            null,
        ],
        [
            [''],
            null,
        ],
    ]);

    it('can decode array of hash ids', function (): void {
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        $encodedArray = [
            $sut->encodeOrFail(1),
            $sut->encodeOrFail(2),
            $sut->encodeOrFail(3),
        ];

        $result = $sut->decodeOrFail(...$encodedArray);

        expect($result)->toBe([1, 2, 3]);
    });

    it('can encode or null', function (array $numbers, string|null $expectation): void {
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        $result = $sut->encode(...$numbers);

        expect($result)->toBe($expectation);
    })->with([
        [[10], fn () => hashids()->encodeOrFail(10)],
        [['15'], fn () => hashids()->encodeOrFail(15)],
        [[10, 20], fn () => hashids()->encodeOrFail(10, 20)],
        [[10, '20'], fn () => hashids()->encodeOrFail(10, 20)],
        [[], null],
    ]);

    it('can encode or throw', function (array $numbers, string|null $expectation): void {
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        expect(static function () use ($sut, $numbers) {
            $sut->encodeOrFail(...$numbers);
        })->when(
            is_null($expectation),
            fn (Expectation $ex) => $ex
                ->toThrow(InvalidArgumentException::class),
        )->when(
            !is_null($expectation),
            fn (Expectation $ex) => $ex
                ->toBe($ex->value),
        );
    })->with([
        [[10], fn () => hashids()->encodeOrFail(10)],
        [[], null],
    ]);

    it('delegates method calls', function (): void {
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        expect($sut->connection())->toBeInstanceOf(Hashids::class);
    });

    it('prioritize macro methods if it exists when delegating method calls', function (): void {
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        HashidsManagerDecorator::macro('getDefaultConnection', fn () => 'something');

        expect($sut->getDefaultConnection())->toBe('something');
    });
})->covers(HashidsManagerDecorator::class);
