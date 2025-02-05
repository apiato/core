<?php

namespace Tests\Unit\Foundation\Support\Traits;

use Apiato\Abstract\Requests\Request;
use Pest\Expectation;

describe(class_basename(Request::class), function (): void {
    it('skips decoding if disabled', function (bool $enabled): void {
        config(['apiato.hash-id' => $enabled]);
        $hashId = hashids()->tryEncode(123);
        $data = ['id' => $hashId];
        $sut = (new class extends Request {
            public function setDecodeArray(array $decode): void
            {
                $this->decode = $decode;
            }
        })->merge($data);
        $sut->setDecodeArray(['id']);

        $result = $sut->all();

        expect($result)
            ->when($enabled, fn (Expectation $ex) => $ex->toBe(['id' => 123]))
            ->when(!$enabled, fn (Expectation $ex) => $ex->toBe($data));
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
