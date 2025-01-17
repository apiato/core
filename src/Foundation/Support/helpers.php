<?php

use Apiato\Foundation\Apiato;

if (!function_exists('shared_path')) {
    /**
     * Get the path to the application's shared directory.
     */
    function shared_path(string $path = ''): string
    {
        return Apiato::instance()->sharedPath($path);
    }
}
