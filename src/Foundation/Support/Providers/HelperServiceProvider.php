<?php

declare(strict_types=1);

namespace Apiato\Foundation\Support\Providers;

use Apiato\Core\Providers\ServiceProvider;
use Apiato\Foundation\Apiato;

final class HelperServiceProvider extends ServiceProvider
{
    public function boot(Apiato $apiato): void
    {
        foreach ($apiato->helpers() as $path) {
            require_once $path;
        }
    }
}
