<?php

use Apiato\Foundation\Apiato;
use Apiato\Support\HashidsManagerDecorator;

if (!function_exists('apiato')) {
    /**
     * Get the Apiato instance.
     */
    function apiato(): Apiato
    {
        return app(Apiato::class);
    }
}

if (!function_exists('shared_path')) {
    /**
     * Get the path to the application's shared directory.
     */
    function shared_path(string $path = ''): string
    {
        return Apiato::instance()->sharedPath($path);
    }
}

if (!function_exists('hashids')) {
    /**
     * Get the Hashids instance.
     */
    function hashids(): HashidsManagerDecorator
    {
        return app('hashids');
    }
}
