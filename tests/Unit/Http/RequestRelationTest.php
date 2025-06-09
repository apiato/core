<?php

declare(strict_types=1);

use Apiato\Http\RequestRelation;
use Workbench\App\Containers\Identity\User\Models\User;

describe(class_basename(RequestRelation::class), function (): void {
    it('can parse requested includes', function (string|array $include): void {
        request()->merge(['include' => $include]);
        $sut = new RequestRelation(request());

        $result = $sut->parseIncludes();

        expect($result)->toBe(['books', 'children', 'children.books']);
    })->with([
        'array' => [
            'include' => ['books', 'children.books'],
        ],
        'csv string' => [
            'include' => 'books,children.books',
        ],
    ]);

    it('returns valid includes', function (array $include, array $expected): void {
        request()->merge(['include' => $include]);
        $sut = new RequestRelation(request());

        $result = $sut->getValidRelationsFor(new User());

        expect($result)->toEqualCanonicalizing($expected);
    })->with([
        [['books.author', 'invalidRelation', 'parent.children.parent'], ['books.author', 'parent.children.parent']],
        [['books.invalidRelation'], ['books']],
        [['invalidRelation'], []],
        [[], []],
        [['books'], ['books']],
    ]);

    it('converts include name to camel case', function (string $includeName, string $expected): void {
        $result = RequestRelation::figureOutRelationName($includeName);

        expect($result)->toBe($expected);
    })->with([
        ['user-profile', 'userProfile'],
        ['user_profile', 'userProfile'],
        ['user-profile_data', 'userProfileData'],
        ['', ''],
        ['user', 'user'],
    ]);
})->covers(RequestRelation::class);
