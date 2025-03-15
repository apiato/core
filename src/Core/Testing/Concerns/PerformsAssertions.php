<?php

namespace Apiato\Core\Testing\Concerns;

use Illuminate\Support\Facades\Schema;

trait PerformsAssertions
{
    /**
     * Assert if the given database table has the expected columns with the expected types.
     *
     * @param string $table the table name
     * @param array<string, string> $expectedColumns The key is the column name and the value is the column type.
     *
     * Example: $this->assertDatabaseTable('users', ['id' => 'bigint']);
     */
    protected function assertDatabaseTable(string $table, array $expectedColumns): void
    {
        $this->assertSameSize($expectedColumns, Schema::getColumnListing($table), "Column count mismatch for '{$table}' table.");
        foreach ($expectedColumns as $column => $type) {
            $this->assertTrue(Schema::hasColumn($table, $column), "Column '{$column}' not found in '{$table}' table.");
            $this->assertEquals($type, Schema::getColumnType($table, $column), "Column '{$column}' in '{$table}' table does not match expected {$type} type.");
        }
    }
}
