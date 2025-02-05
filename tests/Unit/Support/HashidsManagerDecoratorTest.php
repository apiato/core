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

    it('can decode or null', function (string $hashId, int|null $expectation): void {
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        $result = $sut->tryDecode($hashId);

        expect($result)->toBe($expectation);
    })->with([
        [fn () => hashids()->encode(10), 10],
        ['invalid', null],
    ]);

    it('can decode or throw an exception', function (string $hashId, int|null $expectation): void {
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        expect(static function () use ($sut, $hashId) {
            $sut->decode($hashId);
        })->when(
            is_null($expectation),
            fn (Expectation $ex) => $ex
                    ->toThrow(InvalidArgumentException::class),
        )->unless(is_null($expectation), fn (Expectation $ex) => $ex
            ->toBe($ex->value));
    })->with([
        [fn () => hashids()->encode(10), 10],
        ['invalid', null],
    ]);

    it('can encode or null', function (array $numbers, string|null $expectation): void {
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        $result = $sut->tryEncode(...$numbers);

        expect($result)->toBe($expectation);
    })->with([
        [[10], fn () => hashids()->encode(10)],
        [[10, 12], fn () => hashids()->encode(10, 12)],
        [[], null],
    ]);

    it('can encode or throw', function (array $numbers, string|null $expectation): void {
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        expect(static function () use ($sut, $numbers) {
            $sut->encode(...$numbers);
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
        [[10], fn () => hashids()->encode(10)],
        [[], null],
    ]);

    it('can decode array of hash ids', function (): void {
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        $encodedArray = [
            $sut->encode(1),
            $sut->encode(2),
            $sut->encode(3),
        ];

        $result = $sut->decodeArray($encodedArray);

        expect($result)->toBe([1, 2, 3]);
    });

    it('can decode hash ids', function (array $data, array $decode, array $expected): void {
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        $result = $sut->decodeFields(recursiveEncode($data), $decode);

        expect($result)->toBe($expected);
    })->with([
        'top level value' => [
            ['id' => 1],
            ['id'],
            ['id' => 1],
        ],
        'top level empty string' => [
            ['id' => ''],
            ['id'],
            ['id' => ''],
        ],
        'nested value' => [
            ['data' => ['id' => 1]],
            ['data.id'],
            ['data' => ['id' => 1]],
        ],
        'array' => [
            ['ids' => [1, 2]],
            ['ids.*'],
            ['ids' => [1, 2]],
        ],
        'nested array' => [
            ['nested' => ['ids' => [1, 2]]],
            ['nested.ids.*'],
            ['nested' => ['ids' => [1, 2]]],
        ],
        'string non existent key - should return value as is' => [
            ['non_existent_key' => 'value'],
            ['id'],
            ['non_existent_key' => 'value'],
        ],
        'null top level value' => [
            ['id' => null],
            ['id'],
            ['id' => null],
        ],
        'null nested value' => [
            ['data' => ['id' => null]],
            ['data.id'],
            ['data' => ['id' => null]],
        ],
        'null array' => [
            ['ids' => [null, null]],
            ['ids.*'],
            ['ids' => [null, null]],
        ],
        'null nested array' => [
            ['nested' => ['ids' => [null, null]]],
            ['nested.ids.*'],
            ['nested' => ['ids' => [null, null]]],
        ],
    ]);

    function recursiveEncode(array $data): array
    {
        return array_map(static function ($value) {
            if (is_array($value)) {
                return recursiveEncode($value);
            }
            if (is_int($value)) {
                return hashids()->tryEncode($value);
            }

            return $value;
        }, $data);
    }

    it('can decode nested associative arrays', function (): void {
        $data = ['nested' => ['ids' => [['first' => 1, 'second' => hashids()->tryEncode(2)]]]];
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        $result = $sut->decodeFields($data, ['nested.ids.*.second']);

        expect($result)->toBe(['nested' => ['ids' => [['first' => 1, 'second' => 2]]]]);
    });

    it('throws in case of invalid hash id', function (array $data, array $decode): void {
        $sut = new HashidsManagerDecorator(new HashidsManager(config(), app('hashids.factory')));

        expect(fn () => $sut->decodeFields($data, $decode))
            ->toThrow(RuntimeException::class);
    })->with([
        'top level value' => [
            ['id' => 'invalid'],
            ['id'],
        ],
        'nested value' => [
            ['data' => ['id' => 'invalid']],
            ['data.id'],
        ],
        'array' => [
            ['ids' => ['invalid', 'invalid']],
            ['ids.*'],
        ],
        'nested array' => [
            ['nested' => ['ids' => ['invalid', 'invalid']]],
            ['nested.ids.*'],
        ],
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
