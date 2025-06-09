<?php

declare(strict_types=1);

namespace Apiato\Foundation\Support\Providers;

use Apiato\Core\Providers\ServiceProvider;
use Apiato\Foundation\Apiato;

final class LocalizationServiceProvider extends ServiceProvider
{
    public function boot(Apiato $apiato): void
    {
        $localization = $apiato->localization();
        foreach ($localization->paths() as $path) {
            $this->loadTranslationsFrom($path, $localization->buildNamespaceFor($path));
            $this->loadJsonTranslationsFrom($path);
        }
    }
}
