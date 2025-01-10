<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Author\Providers;

use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Providers\ServiceProvider;

class DeferredServiceProvider extends ServiceProvider
{
    public array $aliases = [
        'Baz' => self::class,
    ];

    public function register(): void
    {
    }
}
