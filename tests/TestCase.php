<?php

namespace Tests;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Vinkla\Hashids\Facades\Hashids;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;
    use RefreshDatabase;

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

    protected function defineEnvironment($app)
    {
        tap($app['config'], static function (Repository $config) {
            $config->set('core.tests.running', true);
        });
    }
}
