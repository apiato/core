<?php

declare(strict_types=1);

namespace Apiato\Foundation\Support\Providers;

use Apiato\Core\Providers\ServiceProvider;
use Apiato\Foundation\Apiato;

final class ViewServiceProvider extends ServiceProvider
{
    public function boot(Apiato $apiato): void
    {
        $view = $apiato->view();
        foreach ($view->paths() as $path) {
            $this->loadViewsFrom($path, $view->buildNamespaceFor($path));
        }
    }
}
