<?php

namespace Apiato\Core\Traits\TestTraits\PhpUnit;

use Apiato\Core\Abstracts\Models\Model;
use Illuminate\Auth\Access\Gate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use JetBrains\PhpStorm\Deprecated;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;

trait TestAssertionHelperTrait
{
    /**
     * Assert that the Gate::allows() method is called once with the given arguments.
     *
     * @return Gate|(Gate&MockObject)|MockObject
     *
     * @throws Exception
     */
    protected function getGateMock(string $policyMethodName, ...$args)
    {
        $gateMock = $this->createMock(Gate::class);
        $gateMock->expects($this->once())
            ->method('allows')
            ->with($policyMethodName, ...$args)
            ->willReturn(true);

        return $gateMock;
    }

    /**
     * Assert that the model casts field is empty.
     * By default, the model casts will have 'id' and 'deleted_at' fields (given model is soft deletable).
     * This method will exclude those fields from the assertion.
     * If you want to add more fields, you can pass them as an array.
     */
    protected function assertModelCastsIsEmpty(Model $model, array ...$extraDefaultField): void
    {
        $defaultCasts = [
            'id' => 'int',
            'deleted_at' => 'datetime',
        ];

        $casts = [...$defaultCasts, ...$extraDefaultField];

        $this->assertEmpty(array_diff($model->getCasts(), $casts));
    }

    /**
     * Check if the given id is in the given model collection by comparing hashed ids.
     *
     * @param Collection|array $ids either a collection of models or an array of ids
     *
     * @example $this->inIds($hashedId, $collectionOfModels);
     */
    #[Deprecated(reason: 'Use inIds() helper function instead.')]
    protected function inIds(string $hashedId, Collection|array $ids): bool
    {
        if ($ids instanceof Collection) {
            return $ids->contains('id', $this->decode($hashedId));
        }

        return in_array($this->decode($hashedId), $ids, true);
    }

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

    /**
     * Get the given inaccessible (private/protected) property value.
     *
     * @throws \ReflectionException
     */
    protected function getInaccessiblePropertyValue(object $object, string $property): mixed
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);

        return $property->getValue($object);
    }
}
