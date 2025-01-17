<?php

namespace Apiato\Foundation\Support\Providers;

use Apiato\Abstract\Providers\ServiceProvider;
use Apiato\Foundation\Apiato;
use Illuminate\Support\Facades\File;

final class ConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach (Apiato::instance()->configPaths() as $dir) {
            foreach (File::files($dir) as $file) {
                $this->mergeConfigFrom($file->getPathname(), $file->getFilenameWithoutExtension());
            }
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
