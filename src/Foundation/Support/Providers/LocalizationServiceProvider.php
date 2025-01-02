<?php

namespace Apiato\Foundation\Support\Providers;

use Apiato\Abstract\Providers\ServiceProvider;
use Apiato\Foundation\Apiato;

class LocalizationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $configuration = Apiato::instance()->localization();
        foreach ($configuration->paths() as $path) {
            $this->loadTranslationsFrom($path, $configuration->buildNamespaceFor($path));
            $this->loadJsonTranslationsFrom($path);
        }
    }
}
