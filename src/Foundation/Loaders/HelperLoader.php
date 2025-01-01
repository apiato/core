<?php

namespace Apiato\Foundation\Loaders;

use Apiato\Foundation\Apiato;
use Illuminate\Support\Facades\File;

final readonly class HelperLoader implements Loader
{
    private function __construct(private array $paths)
    {
    }

    public static function create(): self
    {
        return new self(Apiato::instance()->helperPaths());
    }

    public function load(): void
    {
        foreach ($this->paths as $path) {
            $files = File::files($path);

            foreach ($files as $file) {
                require_once $file;
            }
        }
    }
}
