<?php

declare(strict_types=1);

use Apiato\Foundation\Support\Providers\MigrationServiceProvider;
use Illuminate\Support\Facades\Schema;

describe(class_basename(MigrationServiceProvider::class), function (): void {
    it('runs migrations from specified directories', function (): void {
        expect(Schema::hasTable('books'))->toBeTrue()
            ->and(Schema::hasTable('ship_test_table'))->toBeTrue();
    });
})->covers(MigrationServiceProvider::class);
