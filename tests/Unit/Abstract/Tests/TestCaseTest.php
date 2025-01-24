<?php

namespace Tests\Unit\Abstract\Tests;

use Apiato\Abstract\Tests\TestCase;
use Apiato\Foundation\Support\Traits\HashId;
use Apiato\Foundation\Support\Traits\Testing\Assertions;
use Apiato\Foundation\Support\Traits\Testing\RequestHelper;
use Apiato\Foundation\Support\Traits\Testing\TestingUser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

beforeEach(function (): void {
    $this->sut = new class('Anonym') extends TestCase {};
});
describe(class_basename(TestCase::class), function (): void {
    it('uses expected traits', function (): void {
        $expectedTraits = [
            TestingUser::class,
            RequestHelper::class,
            Assertions::class,
            HashId::class,
            LazilyRefreshDatabase::class,
        ];

        expect(TestCase::class)->toUseTraits($expectedTraits);
    });

    it('can detect invalid endpoint format', function (string $endpoint): void {
        expect(fn () => $this->sut->endpoint($endpoint)->parseEndpoint())
            ->toThrow(\RuntimeException::class);
    })->with([
        '',
        'post',
        'get@',
        '@users',
    ]);
})->coversClass(TestCase::class);
