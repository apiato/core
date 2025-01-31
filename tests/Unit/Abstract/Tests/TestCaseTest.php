<?php

namespace Tests\Unit\Abstract\Tests;

use Apiato\Abstract\Tests\TestCase;
use Apiato\Support\Testing\Traits\Assertions;
use Apiato\Support\Testing\Traits\RequestHelper;
use Apiato\Support\Testing\Traits\TestingUser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

beforeEach(function (): void {
    $this->sut = new class('Anonym') extends TestCase {};
});
describe(class_basename(TestCase::class), function (): void {
    it('uses expected traits', function (): void {
        $traits = [
            TestingUser::class,
            RequestHelper::class,
            Assertions::class,
            LazilyRefreshDatabase::class,
        ];

        foreach ($traits as $trait) {
            expect(class_uses_recursive($this->sut))->toContain($trait);
        }
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
})->covers(TestCase::class);
