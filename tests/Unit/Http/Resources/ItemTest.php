<?php

declare(strict_types=1);

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
        function (null|User|LaravelCollection|stdClass|array|Iterator $data, string $expectation): void {
            $sut = new Item($data);

            expect($sut->getResourceKey())->toBe($expectation, json_encode($data));
        },
    )->with([
        [[], ''],
        fn (): array => [[User::factory()->makeOne()], ''],
        fn (): array => [User::factory(2)->make(), ''],
        fn (): array => [User::factory(2)->make()->getIterator(), ''],
        [
            [new class () {
            },
            ], '',
        ],
        fn (): array => [collect([new class () {
        },
        ]), '',
        ],
        fn (): array => [collect([new class () {
        },
        ])->getIterator(), '',
        ],
        fn (): array => [User::factory()->makeOne(), 'User'],
        fn (): array => [new stdClass(), ''],
    ]);
})->covers(Item::class);
