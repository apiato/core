<?php

use Apiato\Abstract\Requests\Request;
use Apiato\Support\Sanitizer;

describe(class_basename(Sanitizer::class), function (): void {

    it('can sanitize', function (array $requestData, array $sanitizeData, mixed $expectation): void {
        $request = new class extends Request {};
        $request->merge([
            ...$requestData,
            'something' => 'that should be removed',
        ]);

        $result = Sanitizer::sanitize($request->all(), $sanitizeData);

        expect($result)->toBe($expectation);
    })->with([
        'null' => [
            ['id' => null],
            ['id'],
            ['id' => null],
        ],
        'string' => [
            ['id' => 'a_string'],
            ['id'],
            ['id' => 'a_string'],
        ],
        'false' => [
            ['id' => false],
            ['id'],
            ['id' => false],
        ],
        'true' => [
            ['id' => true],
            ['id'],
            ['id' => true],
        ],
        'empty string' => [
            ['id' => ''],
            ['id'],
            ['id' => ''],
        ],
        'empty array' => [
            ['id' => []],
            ['id'],
            ['id' => []],
        ],
        'dot notation' => [
            ['data' => ['id' => 12]],
            ['data.id'],
            ['data' => ['id' => 12]],
        ],
        'with default value' => [
            [],
            ['name' => 'Gandalf'],
            ['name' => 'Gandalf'],
        ],
        'dot notation with default value' => [
            [],
            ['data.name' => 'Gandalf'],
            ['data' => ['name' => 'Gandalf']],
        ],
    ]);
})->covers(Sanitizer::class);
