<?php

use Apiato\Http\Resources\Item;
use Illuminate\Support\Collection as LaravelCollection;
use Workbench\App\Containers\Identity\User\Models\User;

describe(class_basename(Item::class), function (): void {
    it('prioritizes the resource key from the resource', function (): void {
        $sut = new Item(resourceKey: 'override');

        expect($sut->getResourceKey())->toBe('override');
    });

    it(
        'can guess the resource key from the data',
        function (User|LaravelCollection|stdClass|array|Iterator|null $data, string $expectation): void {
            $sut = new Item($data);

            expect($sut->getResourceKey())->toBe($expectation, json_encode($data));
        },
    )->with([
        [[], ''],
        fn () => [[User::factory()->makeOne()], ''],
        fn () => [User::factory(2)->make(), ''],
        fn () => [User::factory(2)->make()->getIterator(), ''],
        [[new class {}], ''],
        fn () => [collect([new class {}]), ''],
        fn () => [collect([new class {}])->getIterator(), ''],
        fn () => [User::factory()->makeOne(), 'User'],
        fn () => [new stdClass(), ''],
    ]);
})->covers(Item::class);
