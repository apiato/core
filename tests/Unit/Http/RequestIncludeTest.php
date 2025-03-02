<?php

namespace Tests\Unit\Support;

use Apiato\Http\RequestInclude;
use Workbench\App\Containers\Identity\User\Models\User;

describe(class_basename(RequestInclude::class), function (): void {
    it('can parse requested includes', function (string|array $include): void {
        request()->merge(['include' => $include]);
        $sut = new RequestInclude(request());

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
        $sut = new RequestInclude(request());

        $result = $sut->getValidIncludesFor(new User());

        expect($result)->toEqualCanonicalizing($expected);
    })->with([
        [['books.author', 'invalidRelation', 'parent.children.parent'], ['books.author', 'parent.children.parent']],
        [['books.invalidRelation'], ['books']],
        [['invalidRelation'], []],
        [[], []],
        [['books'], ['books']],
    ]);

    it('converts include name to camel case', function (string $includeName, string $expected): void {
        $result = RequestInclude::figureOutRelationName($includeName);

        expect($result)->toBe($expected);
    })->with([
        ['user-profile', 'userProfile'],
        ['user_profile', 'userProfile'],
        ['user-profile_data', 'userProfileData'],
        ['', ''],
        ['user', 'user'],
    ]);
})->covers(RequestInclude::class);
