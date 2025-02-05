<?php

namespace Tests\Unit\Foundation\Support\Traits;

use Apiato\Abstract\Requests\Request;
use Pest\Expectation;

describe(class_basename(Request::class), function (): void {
    it('skips decoding if disabled', function (bool $enabled): void {
        config(['apiato.hash-id' => $enabled]);
        $hashId = hashids()->tryEncode(123);
        $data = [
            'id' => $hashId,
            'name' => 'Gandalf',
        ];
        $sut = (new class extends Request {
            public function setDecodeArray(array $decode): void
            {
                $this->decode = $decode;
            }
        })->merge($data);
        $sut->setDecodeArray(['id']);

        expect($sut->all())
            ->when(
                $enabled,
                fn (Expectation $ex) => $ex->toBe(
                    [
                        'id' => 123,
                        'name' => 'Gandalf',
                    ],
                ),
            )->when(!$enabled, fn (Expectation $ex) => $ex->toBe($data))
            ->and($sut->all(['id']))
            ->when($enabled, fn (Expectation $ex) => $ex->toBe(['id' => 123]))
            ->when(!$enabled, fn (Expectation $ex) => $ex->toBe(['id' => $hashId]));
    })->with([
        [true],
        [false],
    ]);

    it('has the sanitize method', function (): void {
        $sut = new class extends Request {
        };
        $sut->merge([
            'name' => 'Gandalf',
            'age' => 100,
        ]);

        $result = $sut->sanitize(['age']);

        expect($result)->toBe(['age' => 100]);
    });
})->covers(Request::class);
