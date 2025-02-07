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
        $nestedIds = [1, 2];
        $nestedIdsHashed = [
            hashids()->encode($nestedIds[0]),
            hashids()->encode($nestedIds[1]),
        ];
        $ids = [2, 1];
        $hashedIds = [
            hashids()->encode($ids[0]),
            hashids()->encode($ids[1]),
        ];
        $result = $this->patchJson("v1/books/{$bookIdHashed}", [
            'title' => 'New Title',
            'author_id' => $authorIdHashed,
            'authors' => [
                [
                    'id' => $authorIdHashed,
                    'name' => 'Author Name',
                ],
            ],
            'ids' => $hashedIds,
            'just_a_number' => 123,
            'nested' => [
                'id' => $nestedIdHashed,
                'ids' => $nestedIdsHashed,
            ],
        ]);
        $expectedBookId = $enabled ? $bookId : $bookIdHashed;
        $expectedAuthorId = $enabled ? $authorId : $authorIdHashed;
        $expectedNestedId = $enabled ? $nestedId : $nestedIdHashed;
        $expectedNestedIds = $enabled ? $nestedIds : $nestedIdsHashed;
        $expectedIds = $enabled ? $ids : $hashedIds;

        expect($result->json())
            ->toBe([
                'input(val)' => [
                    'id' => null,
                    'id-default' => 100,
                    'title' => 'New Title',
                    'nested.id' => $expectedNestedId,
                    'nested.with-default' => 200,
                    'author_id' => $expectedAuthorId,
                    'authors' => [
                        ['id' => $expectedAuthorId, 'name' => 'Author Name'],
                    ],
                    'authors.*.id' => [$expectedAuthorId],
                    'authors.*.with-default' => [null],
                    'ids' => $expectedIds,
                    'with-default' => [1, 2, 3],
                    'none_existing' => null,
                    'optional_id' => null,
                ],
                'all(val)' => [
                    'id' => [
                        'id' => null,
                    ],
                    'title' => [
                        'title' => 'New Title',
                    ],
                    'nested.id' => [
                        'nested' => [
                            'id' => $expectedNestedId,
                        ],
                    ],
                    'nested.ids' => [
                        'nested' => [
                            'ids' => $expectedNestedIds,
                        ],
                    ],
                    'author_id' => [
                        'author_id' => $expectedAuthorId,
                    ],
                    'none_existing' => [
                        'none_existing' => null,
                    ],
                    'optional_id' => [
                        'optional_id' => null,
                    ],
                ],
                'route(val)' => [
                    'id' => $expectedBookId,
                    'none_existing' => null,
                ],
                'request->val' => [
                    'id' => $expectedBookId,
                    'title' => 'New Title',
                    'none_existing' => null,
                    'optional_id' => null,
                ],
                'input()' => [
                    'title' => 'New Title',
                    'author_id' => $expectedAuthorId,
                    'authors' => [
                        ['id' => $expectedAuthorId, 'name' => 'Author Name'],
                    ],
                    'ids' => $expectedIds,
                    'just_a_number' => 123,
                    'nested' => [
                        'id' => $expectedNestedId,
                        'ids' => $expectedNestedIds,
                    ],
                ],
                'all()' => [
                    'title' => 'New Title',
                    'author_id' => $expectedAuthorId,
                    'authors' => [
                        ['id' => $expectedAuthorId, 'name' => 'Author Name'],
                    ],
                    'ids' => $expectedIds,
                    'just_a_number' => 123,
                    'nested' => [
                        'id' => $expectedNestedId,
                        'ids' => $expectedNestedIds,
                    ],
                ],
                'validated' => [
                    'title' => 'New Title',
                    'author_id' => $expectedAuthorId,
                    'nested' => [
                        'id' => $expectedNestedId,
                        'ids' => $expectedNestedIds,
                    ],
                    'ids' => $expectedIds,
                    'authors' => [
                        ['id' => $expectedAuthorId],
                    ],
                ],
                'route()::class' => Illuminate\Routing\Route::class,
            ]);
    })->with([
        [true],
        [false],
    ]);
})->covers(Request::class);
