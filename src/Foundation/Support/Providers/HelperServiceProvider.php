<?php

namespace Apiato\Foundation\Support\Providers;

use Apiato\Abstract\Providers\ServiceProvider;
use Apiato\Foundation\Apiato;
use Illuminate\Support\Facades\File;

final class HelperServiceProvider extends ServiceProvider
{
    public function boot(Apiato $apiato): void
    {
        foreach ($apiato->helperPaths() as $path) {
            $files = File::files($path);

            foreach ($files as $file) {
                require_once $file;
            }
        }
    }
}
