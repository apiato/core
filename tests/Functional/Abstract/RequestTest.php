<?php

use Apiato\Abstract\Requests\Request;

describe(class_basename(Request::class), function (): void {
    beforeEach(function (): void {
        config(['apiato.hash-id' => true]);
    });

    it('can decode specified ids', function (): void {
        $bookId = hashids()->encode(5);
        $result = $this->patchJson("v1/books/{$bookId}", [
            'title' => 'New Title',
            'author_id' => hashids()->encode(10),
            'nested' => [
                'id' => hashids()->encode(15),
            ],
        ]);

        expect($result->json())
            ->toBe([
                'all' => [
                    'title' => 'New Title',
                    'author_id' => 10,
                    'nested' => [
                        'id' => 15,
                    ],
                    'id' => 5,
                ],
                'id' => 5,
                'validated' => [
                    'id' => 5,
                ],
            ]);
    });
})->covers(Request::class);
