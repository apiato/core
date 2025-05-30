<?php

namespace Apiato\Foundation\Support\Providers;

use Apiato\Core\Providers\ServiceProvider;
use Apiato\Foundation\Apiato;

final class ViewServiceProvider extends ServiceProvider
{
    public function boot(Apiato $apiato): void
    {
        $configuration = $apiato->view();
        foreach ($configuration->paths() as $path) {
            $this->loadViewsFrom($path, $configuration->buildNamespaceFor($path));
        }
    }
}
