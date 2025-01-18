<?php

namespace Apiato\Generator\Traits;

trait ParserTrait
{
    /**
     * replaces the variables in the path structure with defined values.
     */
    public function parsePathStructure($path, $data): string|array
    {
        $path = str_replace(array_map([$this, 'maskPathVariables'], array_keys($data)), array_values($data), $path);

        return str_replace('*', $this->parsedFileName, $path);
    }

    /**
     * replaces the variables in the file structure with defined values.
     */
    public function parseFileStructure($filename, $data): string|array
    {
        return str_replace(array_map([$this, 'maskFileVariables'], array_keys($data)), array_values($data), $filename);
    }

    /**
     * replaces the variables in the stub file with defined values.
     */
    public function parseStubContent($stub, $data): string|array
    {
        return str_replace(array_map([$this, 'maskStubVariables'], array_keys($data)), array_values($data), $stub);
    }

    private function maskPathVariables(string $key): string
    {
        return '{' . $key . '}';
    }

    private function maskFileVariables(string $key): string
    {
        return '{' . $key . '}';
    }

    private function maskStubVariables(string $key): string
    {
        return '{{' . $key . '}}';
    }
}
