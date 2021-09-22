<?php

if (!function_exists('uncamelize')) {
    /**
     * @param $word
     * @param string $splitter
     * @param bool $uppercase
     * @return string|string[]|null
     */
    function uncamelize($word, string $splitter = " ", bool $uppercase = true): array|string|null
    {
        $word = preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0',
            preg_replace('/(?!^)[[:upper:]]+/', $splitter . '$0', $word));

        return $uppercase ? ucwords($word) : $word;
    }
}
