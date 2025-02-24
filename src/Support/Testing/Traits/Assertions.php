<?php

namespace Apiato\Support\Testing\Traits;

use Apiato\Abstract\Models\Model;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;

trait Assertions
{
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
     * Create a spy for an Action, SubAction or a Task that uses a repository.
     *
     * @param string $className the Action, SubAction or a Task class name
     * @param string $repositoryClassName the repository class name
     */
    protected function createSpyWithRepository(string $className, string $repositoryClassName, bool $allowRun = true): MockInterface
    {
        /** @var MockInterface $taskSpy */
        $taskSpy = \Mockery::mock($className, [app($repositoryClassName)])
            ->shouldIgnoreMissing(null, true)
            ->makePartial();

        if ($allowRun) {
            $taskSpy->allows('run')->andReturn();
        }

        $this->swap($className, $taskSpy);

        return $taskSpy;
    }

    /**
     * Mock a repository and assert that the given criteria is pushed to it.
     *
     * @param string $repositoryClassName the repository class name
     * @param string $criteriaClassName the criteria class name
     * @param array<string, mixed>|null $criteriaArgs the criteria constructor arguments
     *
     * @return MockInterface repository mock
     *
     * @example $this->
     * assertCriteriaPushedToRepository(UserRepository::class, SearchUsersCriteria::class, ['parameterName' => 'value']);
     */
    protected function assertCriteriaPushedToRepository(string $repositoryClassName, string $criteriaClassName, array|null $criteriaArgs = null): MockInterface
    {
        $repositoryMock = $this->mock($repositoryClassName);

        if (is_null($criteriaArgs)) {
            $repositoryMock->expects('pushCriteria')->once();
        } else {
            $repositoryMock->expects('pushCriteriaWith')->once()->with($criteriaClassName, $criteriaArgs);
        }

        return $repositoryMock;
    }

    /**
     * Assert that no criteria are pushed to the repository.
     *
     * @param string $repositoryClassName the repository class name
     *
     * @return MockInterface repository mock
     */
    protected function assertNoCriteriaPushedToRepository(string $repositoryClassName): MockInterface
    {
        $repositoryMock = $this->mock($repositoryClassName);
        $repositoryMock->expects('pushCriteria')->never();

        return $repositoryMock;
    }

    /**
     * Allow "addRequestCriteria" method invocation on the repository mock.
     * This is particularly useful when you want to test a repository that uses the RequestCriteria
     * (e.g., for search and filtering).
     */
    protected function allowAddRequestCriteriaInvocation(MockInterface $repositoryMock): void
    {
        $repositoryMock->allows('addRequestCriteria')->andReturnSelf();
    }
}
