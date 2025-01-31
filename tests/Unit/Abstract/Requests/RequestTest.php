<?php

namespace Tests\Unit\Foundation\Support\Traits;

use Apiato\Abstract\Requests\Request;

describe(class_basename(Request::class), function (): void {
    beforeEach(function (): void {
        config(['apiato.hash-id' => true]);
    });

    function getSut(): Request
    {
        return new class extends Request {
            public function publicDecodeHashedIds(array $requestData): array
            {
                return $this->decodeHashedIds($requestData);
            }

            public function setDecodeArray(array $decode): void
            {
                $this->decode = $decode;
            }
        };
    }

    it('can decode ids', function (): void {
        $encoded = hashids()->encode(123);

        $result = getSut()->decode($encoded);

        expect($result)->toBe(123);
    });

    it('returns null for invalid ids', function (): void {
        $result = getSut()->decode('invalid');

        expect($result)->toBeNull();
    });

    it('returns null for null ids', function (): void {
        $result = getSut()->decode(null);

        expect($result)->toBeNull();
    });

    it('can decode array of hash ids', function (): void {
        $encodedArray = [
            hashids()->encode(1),
            hashids()->encode(2),
            hashids()->encode(3),
        ];

        $result = getSut()->decodeArray($encodedArray);

        expect($result)->toBe([1, 2, 3]);
    });

    it('returns true for empty values', function (): void {
        $result = getSut()->skipHashIdDecode('');

        expect($result)->toBeTrue();
    });

    it('returns false for non empty values', function (): void {
        $result = getSut()->skipHashIdDecode('non-empty');

        expect($result)->toBeFalse();
    });

    it('skips decoding if disabled', function (): void {
        config(['apiato.hash-id' => false]);
        $data = ['id' => hashids()->encode(123)];

        $result = getSut()->publicDecodeHashedIds($data);

        expect($result)->toBe($data);
    });

    it('can decode hash ids', function (array $data, array $decode, array $expected): void {
        $data = recursiveEncode($data);
        $sut = getSut();
        $sut->setDecodeArray($decode);

        $result = $sut->publicDecodeHashedIds($data);

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
                return hashids()->encode($value);
            }

            return $value;
        }, $data);
    }

    it('can decode nested associative arrays', function (): void {
        $data = ['nested' => ['ids' => [['first' => 1, 'second' => hashids()->encode(2)]]]];
        $sut = getSut();
        $sut->setDecodeArray(['nested.ids.*.second']);

        $result = $sut->publicDecodeHashedIds($data);

        expect($result)->toBe(['nested' => ['ids' => [['first' => 1, 'second' => 2]]]]);
    });

    it('returns null for non hash strings', function (): void {
        $result = getSut()->decode('non_hash_string');

        expect($result)->toBeNull();
    });

    it('thorws in case of invalid hash id', function (array $data, array $decode): void {
        $sut = getSut();
        $sut->setDecodeArray($decode);

        expect(fn () => $sut->publicDecodeHashedIds($data))
            ->toThrow(\RuntimeException::class);
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
})->covers(Request::class);
