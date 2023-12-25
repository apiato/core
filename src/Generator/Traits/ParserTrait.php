<?php

namespace Apiato\Core\Generator\Traits;

trait ParserTrait
{
    /**
     * replaces the variables in the path structure with defined values.
     */
    public function parsePathStructure($path, $data): string|array
    {
        $path = str_replace(array_map([$this, 'maskPathVariables'], array_keys($data)), array_values($data), $path);
        $path = str_replace('*', $this->parsedFileName, $path);

        return $path;
    }

    /**
     * replaces the variables in the file structure with defined values.
     */
    public function parseFileStructure($filename, $data): string|array
    {
        $filename = str_replace(array_map([$this, 'maskFileVariables'], array_keys($data)), array_values($data), $filename);

        return $filename;
    }

    /**
     * replaces the variables in the stub file with defined values.
     */
    public function parseStubContent($stub, $data): string|array
    {
        $stub = str_replace(array_map([$this, 'maskStubVariables'], array_keys($data)), array_values($data), $stub);

        return $stub;
    }

    private function maskPathVariables($key)
    {
        return '{' . $key . '}';
    }

    private function maskFileVariables($key)
    {
        return '{' . $key . '}';
    }

    private function maskStubVariables($key)
    {
        return '{{' . $key . '}}';
    }
}
