<?php

namespace Apiato\Core\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Vinkla\Hashids\Facades\Hashids;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;

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
