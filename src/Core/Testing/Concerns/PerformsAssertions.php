<?php

declare(strict_types=1);

namespace Apiato\Core\Testing\Concerns;

use Illuminate\Support\Facades\Schema;

trait PerformsAssertions
{
    /**
     * Assert if the given database table has the expected columns with the expected types.
     *
     * @param string                $table           the table name
     * @param array<string, string> $expectedColumns The key is the column name and the value is the column type.
     *
     * Example: self::assertDatabaseTable('users', ['id' => 'bigint']);
     */
    protected static function assertDatabaseTable(string $table, array $expectedColumns): void
    {
        self::assertSameSize($expectedColumns, Schema::getColumnListing($table), \sprintf("Column count mismatch for '%s' table.", $table));
        foreach ($expectedColumns as $column => $type) {
            self::assertTrue(Schema::hasColumn($table, $column), \sprintf("Column '%s' not found in '%s' table.", $column, $table));
            self::assertEquals($type, Schema::getColumnType($table, $column), \sprintf("Column '%s' in '%s' table does not match expected %s type.", $column, $table, $type));
        }
    }
}
