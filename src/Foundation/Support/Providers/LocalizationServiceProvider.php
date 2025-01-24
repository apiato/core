<?php

namespace Apiato\Foundation\Support\Providers;

use Apiato\Abstract\Providers\ServiceProvider;
use Apiato\Foundation\Apiato;

final class LocalizationServiceProvider extends ServiceProvider
{
    public function boot(Apiato $apiato): void
    {
        $configuration = $apiato->localization();
        foreach ($configuration->paths() as $path) {
            $this->loadTranslationsFrom($path, $configuration->buildNamespaceFor($path));
            $this->loadJsonTranslationsFrom($path);
        }
    }
}
