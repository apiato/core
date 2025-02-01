<?php

use Apiato\Http\Resources\Collection;
use Illuminate\Support\Collection as LaravelCollection;
use Workbench\App\Containers\Identity\User\Models\User;

describe(class_basename(Collection::class), function (): void {
    it('prioritizes the resource key from the resource', function (): void {
        $sut = new Collection(resourceKey: 'override');

        expect($sut->getResourceKey())->toBe('override');
    });

    it(
        'can guess the resource key from the data',
        function (User|LaravelCollection|stdClass|array|Iterator|null $data, string $expectation): void {
            $sut = new Collection($data);

            expect($sut->getResourceKey())->toBe($expectation, json_encode($data));
        },
    )->with([
        [[], ''],
        fn () => [[User::factory()->makeOne()], 'User'],
        [fn () => User::factory(2)->make(), 'User'],
        [fn () => User::factory(2)->make()->getIterator(), 'User'],
        [[new class {}], ''],
        [fn () => collect([new class {}]), ''],
        [fn () => collect([new class {}])->getIterator(), ''],
        [fn () => User::factory()->makeOne(), 'User'],
        [fn () => new stdClass(), ''],
    ]);
})->covers(Collection::class);
