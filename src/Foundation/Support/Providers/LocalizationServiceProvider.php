<?php

namespace Apiato\Foundation\Support\Providers;

use Apiato\Abstract\Providers\ServiceProvider;
use Apiato\Foundation\Apiato;

class LocalizationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $localization = Apiato::instance()->localization();
        foreach ($localization->paths() as $path) {
            $this->loadTranslationsFrom($path, $localization->buildNamespaceFor($path));
            $this->loadJsonTranslationsFrom($path);
        }
    }
}
