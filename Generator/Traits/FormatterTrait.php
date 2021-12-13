<?php

namespace Apiato\Core\Generator\Traits;

trait FormatterTrait
{
    /**
     * @param $operation
     * @param $class
     *
     * @return  string
     */
    public function prependOperationToName($operation, $class): string
    {
        $className = ($operation == 'list') ? ngettext($class) : $class;

        return $operation . $this->capitalize($className);
    }

    /**
     * @param $word
     *
     * @return  string
     */
    public function capitalize($word): string
    {
        return ucfirst($word);
    }

    /**
     * @param $string
     *
     * @return  string
     */
    protected function trimString($string): string
    {
        return trim($string);
    }
}
