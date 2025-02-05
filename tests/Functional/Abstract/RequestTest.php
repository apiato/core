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

        $result->assertCreated();
    });
})->covers(Request::class);
