<?php

namespace Apiato\Foundation\Support\Providers;

use Apiato\Abstract\Providers\ServiceProvider;
use Apiato\Foundation\Apiato;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $configuration = Apiato::instance()->view();
        foreach ($configuration->paths() as $path) {
            $this->loadViewsFrom($path, $configuration->buildNamespaceFor($path));
        }
    }
}
