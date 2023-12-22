<?php

namespace Apiato\Core\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Vinkla\Hashids\Facades\Hashids;

use function Orchestra\Testbench\package_path;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;
    use RefreshDatabase;

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(package_path('Tests/Migrations'));
    }

    public function decode(string $hashedId): int|null
    {
        $result = Hashids::decode($hashedId);

        if (empty($result)) {
            return null;
        }

        return $result[0];
    }

    public function encode(int $id): string
    {
        return Hashids::encode($id);
    }
}
