<?php

declare(strict_types=1);

namespace Apiato\Foundation\Support\Providers;

use Apiato\Core\Providers\ServiceProvider;
use Apiato\Foundation\Apiato;
use Illuminate\Support\Str;

final class ConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach (Apiato::instance()->configs() as $path) {
            $this->mergeConfigFrom($path, Str::of($path)
                ->afterLast(DIRECTORY_SEPARATOR)
                ->before('.php')->value());
        }

        $this->mergeConfigFrom(
            __DIR__ . '/../../../../config/apiato.php',
            'apiato',
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../../../config/apiato.php' => shared_path('Configs/apiato.php'),
        ], 'apiato-config');
    }
}
