<?php

namespace Apiato\Generator\Traits;

trait FormatterTrait
{
    public function prependOperationToName(string $operation, $class): string
    {
        $className = ('list' === $operation) ? ngettext($class) : $class;

        return $operation . $this->capitalize($className);
    }

    public function capitalize($word): string
    {
        return ucfirst((string) $word);
    }

    protected function trimString($string): string
    {
        return trim((string) $string);
    }
}
