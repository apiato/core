<?php

namespace Apiato\Foundation\Support\Providers;

use Apiato\Abstract\Providers\ServiceProvider;
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
            realpath('config/apiato.php'),
            'apiato',
        );
    }

    public function boot(): void
    {
        $this->publishes([
            realpath('config/apiato.php') => shared_path('Configs/apiato.php'),
        ], 'apiato-config');
    }
}
