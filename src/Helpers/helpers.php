<?php

use Illuminate\Support\Collection;

if (!function_exists('uncamelize')) {
    /**
     * @return string|string[]|null
     */
    function uncamelize($word, string $splitter = ' ', bool $uppercase = true): array|string|null
    {
        $word = preg_replace(
            '/(?!^)[[:upper:]][[:lower:]]/',
            '$0',
            preg_replace('/(?!^)[[:upper:]]+/', $splitter . '$0', $word),
        );

        return $uppercase ? ucwords($word) : $word;
    }
}

if (!function_exists('inIds')) {
    /**
     * Check if the given id is in the given model collection by comparing hashed ids.
     *
     * @param Collection|array $ids either a collection of models or an array of unhashed ids
     */
    function inIds(string $hashedId, Collection|array $ids): bool
    {
        $hashService = new class() extends \Apiato\Core\Abstracts\Models\Model {
            use \Apiato\Core\Traits\HashIdTrait;
        };

        $id = $hashService->decode($hashedId);
        if ($ids instanceof Collection) {
            return $ids->contains('id', $id);
        }

        return in_array($hashService->decode($hashedId), $ids, true);
    }
}
