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
                'input' => [
                    'title' => 'New Title',
                    'author_id' => 10,
                    'nested' => [
                        'id' => 15,
                    ],
                ],
                'input.id' => null,
                'input.title' => 'New Title',
                'input.nested.id' => 15,
                'input.author_id' => 10,
                'input.none_existing' => null,
                'input.optional_id' => null,
                'all' => [
                    'title' => 'New Title',
                    'author_id' => 10,
                    'nested' => [
                        'id' => 15,
                    ],
                ],
                'all.id' => [
                    'id' => null,
                ],
                'all.title' => [
                    'title' => 'New Title',
                ],
                'all.nested.id' => [
                    'nested' => [
                        'id' => 15,
                    ],
                ],
                'all.author_id' => [
                    'author_id' => 10,
                ],
                'all.none_existing' => [
                    'none_existing' => null,
                ],
                'all.optional_id' => [
                    'optional_id' => null,
                ],
                'route' => Illuminate\Routing\Route::class,
                'route.id' => 5,
                'route.none_existing' => null,
                'request.id' => 5,
                'request.title' => 'New Title',
                'request.none_existing' => null,
                'request.optional_id' => null,
                'validated' => [
                    'title' => 'New Title',
                    'author_id' => 10,
                ],
            ]);
    });
})->covers(Request::class);
