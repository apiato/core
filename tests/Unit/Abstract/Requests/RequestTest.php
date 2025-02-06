<?php

namespace Tests\Unit\Foundation\Support\Traits;

use Apiato\Abstract\Requests\Request;

describe(class_basename(Request::class), function (): void {
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
