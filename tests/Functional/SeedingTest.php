<?php

describe('SeedingTest', function (): void {
    it('should seed the database using seeders from the specified directories', function (): void {
        $this->artisan('db:seed')
            ->assertExitCode(0);

        expect(DB::table('books')->count())->toBe(8);

        $this->artisan('migrate --seed')
            ->assertExitCode(0);

        expect(DB::table('books')->count())->toBe(16);
    });
})->coversNothing();
