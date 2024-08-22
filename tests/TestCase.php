<?php

namespace Apiato\Core\Tests;

use Apiato\Core\Providers\ApiatoServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Vinkla\Hashids\Facades\Hashids;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ((app(ApiatoServiceProvider::class, ['app' => $this->app]))->serviceProviders as $provider) {
            App::register($provider);
        }
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
