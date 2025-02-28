<?php

namespace Tests\Unit\Abstract\Tests;

use Apiato\Abstract\Tests\TestCase;
use Apiato\Abstract\Tests\Concerns\PerformsAssertions;

beforeEach(function (): void {
    $this->sut = new class('Anonym') extends TestCase {};
});
describe(class_basename(TestCase::class), function (): void {
    it('uses expected traits', function (): void {
        $traits = [
            PerformsAssertions::class,
        ];

        foreach ($traits as $trait) {
            expect(class_uses_recursive($this->sut))->toContain($trait);
        }
    });
})->covers(TestCase::class);
