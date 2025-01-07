<?php

namespace Apiato\Foundation\Loaders;

use Apiato\Foundation\Apiato;
use Illuminate\Support\Facades\File;

final readonly class HelperLoader
{
    private function __construct(
        private Apiato $apiato,
    ) {
    }

    public static function create(): self
    {
        return new self(app()->make(Apiato::class));
    }

    public function load(): void
    {
        foreach ($this->apiato->helperPaths() as $path) {
            $files = File::files($path);

            foreach ($files as $file) {
                require_once $file;
            }
        }
    }
}
