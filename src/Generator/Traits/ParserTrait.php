<?php

namespace Apiato\Core\Generator\Traits;

trait ParserTrait
{
    /**
     * Replaces the variables in the stub content with defined values.
     */
    protected function parseStubContent($stub, $data): string|array
    {
        $maskedKeys = array_map(function ($key) {
            return '{{' . $key . '}}';
        }, array_keys($data));

        return str_replace($maskedKeys, array_values($data), $stub);
    }
}
