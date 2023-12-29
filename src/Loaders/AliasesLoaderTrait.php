<?php

namespace Apiato\Core\Loaders;

use Illuminate\Foundation\AliasLoader;

trait AliasesLoaderTrait
{
    public function loadAliases(): void
    {
        // `$this->aliases` is declared on each Container's Main Service Provider
        foreach ($this->aliases ?? [] as $aliasKey => $aliasValue) {
            if (class_exists($aliasValue)) {
                $this->loadAlias($aliasKey, $aliasValue);
            }
        }
    }

    private function loadAlias($aliasKey, $aliasValue): void
    {
        AliasLoader::getInstance()->alias($aliasKey, $aliasValue);
    }
}
