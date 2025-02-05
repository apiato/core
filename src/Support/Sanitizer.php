<?php

namespace Apiato\Support;

use Illuminate\Support\Arr;

final class Sanitizer
{
    /**
     * Sanitize the given source using an array of fields.
     *
     * If the field is provided as an integer key, the source value is set only if it exists.
     * If the field is provided as a string key => default, the source value is used if it exists; otherwise the default value will be set.
     *
     * This preserves false and null values, and supports dot-notation keys.
     *
     * Example:
     *  sanitize($source, [
     *      'id',
     *      'email',
     *      'name' => 'Gandalf',
     *      'another.name' => 'Saruman',
     *  ]);
     *
     * @param array<array-key, string> $source the source array to sanitize
     * @param array<array-key, string> $fields fields in dot-notation, potentially mapped to default values
     *
     * @return array<string, string> the sanitized array matching the requested fields with any defaults as needed
     */
    public static function sanitize(array $source, array $fields): array
    {
        $sanitized = [];

        foreach ($fields as $key => $value) {
            if (is_int($key)) {
                if (Arr::has($source, $value)) {
                    Arr::set($sanitized, $value, Arr::get($source, $value));
                }
            } else {
                Arr::set($sanitized, $key, Arr::get($source, $key, $value));
            }
        }

        return $sanitized;
    }
}
