<?php

namespace Tests\Unit\Abstract\Tests;

use Apiato\Abstract\Tests\TestCase;
use Apiato\Support\Testing\Traits\Assertions;

beforeEach(function (): void {
    $this->sut = new class('Anonym') extends TestCase {};
});
describe(class_basename(TestCase::class), function (): void {
    it('uses expected traits', function (): void {
        $traits = [
            Assertions::class,
        ];

        foreach ($traits as $trait) {
            expect(class_uses_recursive($this->sut))->toContain($trait);
        }
    });
})->covers(TestCase::class);
