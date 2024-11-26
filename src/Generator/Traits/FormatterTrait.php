<?php

namespace Apiato\Core\Generator\Traits;

use Illuminate\Support\Str;

trait FormatterTrait
{
    public function getFileTypeCapitalized(): string
    {
        return Str::ucfirst($this->getFileType());
    }

    protected function removeSpecialChars($str): string
    {
        return preg_replace('/[^A-Za-z0-9]/', '', $str);
    }

    protected function camelize(string $string): string
    {
        return str_replace(
            ' ',
            '',
            ucwords(str_replace(
                ['-', '_'],
                ' ',
                $string,
            )),
        );
    }
}
