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
    public function prependOperationToName($operation, $class)
    {
        $className = ($operation == 'list') ? ngettext($class) : $class;

        return $operation . $this->capitalize($className);
    }

    /**
     * @param $word
     *
     * @return  string
     */
    public function capitalize($word)
    {
        return ucfirst($word);
    }

    /**
     * @param $string
     *
     * @return  string
     */
    protected function trimString($string)
    {
        return trim($string);
    }
}
