<?php

use Apiato\Abstract\Requests\Request;

describe(class_basename(Request::class), function (): void {
    it('can decode specified ids', function (bool $enabled): void {
        config(['apiato.hash-id' => $enabled]);
        $bookId = 5;
        $bookIdHashed = hashids()->encode($bookId);
        $authorId = 10;
        $authorIdHashed = hashids()->encode($authorId);
        $nestedId = 15;
        $nestedIdHashed = hashids()->encode($nestedId);
        $result = $this->patchJson("v1/books/{$bookIdHashed}", [
            'title' => 'New Title',
            'author_id' => $authorIdHashed,
            'just_a_number' => 123,
            'nested' => [
                'id' => $nestedIdHashed,
            ],
        ]);
        $expectedBookId = $enabled ? $bookId : $bookIdHashed;
        $expectedAuthorId = $enabled ? $authorId : $authorIdHashed;
        $expectedNestedId = $enabled ? $nestedId : $nestedIdHashed;

        expect($result->json())
            ->toBe([
                'input' => [
                    'title' => 'New Title',
                    'author_id' => $expectedAuthorId,
                    'just_a_number' => 123,
                    'nested' => [
                        'id' => $expectedNestedId,
                    ],
                ],
                'input.id' => null,
                'input.title' => 'New Title',
                'input.nested.id' => $expectedNestedId,
                'input.author_id' => $expectedAuthorId,
                'input.none_existing' => null,
                'input.optional_id' => null,
                'all' => [
                    'title' => 'New Title',
                    'author_id' => $expectedAuthorId,
                    'just_a_number' => 123,
                    'nested' => [
                        'id' => $expectedNestedId,
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
                        'id' => $expectedNestedId,
                    ],
                ],
                'all.author_id' => [
                    'author_id' => $expectedAuthorId,
                ],
                'all.none_existing' => [
                    'none_existing' => null,
                ],
                'all.optional_id' => [
                    'optional_id' => null,
                ],
                'route' => Illuminate\Routing\Route::class,
                'route.id' => $expectedBookId,
                'route.none_existing' => null,
                'request.id' => $expectedBookId,
                'request.title' => 'New Title',
                'request.none_existing' => null,
                'request.optional_id' => null,
                'validated' => [
                    'title' => 'New Title',
                    'author_id' => $expectedAuthorId,
                ],
            ]);
    })->with([
        [true],
        [false],
    ]);
})->covers(Request::class);
