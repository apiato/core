<?php

if (!function_exists('uncamelize')) {
    /**
     * @param $word
     * @param string $splitter
     * @param bool $uppercase
     * @return string|string[]|null
     */
    function uncamelize($word, $splitter = " ", $uppercase = true)
    {
        $word = preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0',
            preg_replace('/(?!^)[[:upper:]]+/', $splitter . '$0', $word));

        return $uppercase ? ucwords($word) : $word;
    }
}
