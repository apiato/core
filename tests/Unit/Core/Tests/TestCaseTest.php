<?php

declare(strict_types=1);

use Apiato\Core\Testing\Concerns\PerformsAssertions;
use Apiato\Core\Testing\TestCase;

beforeEach(function (): void {
    $this->sut = new class ('Anonym') extends TestCase {
    };
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
