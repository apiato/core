<?php

namespace Apiato\Core\Abstracts\Transporters;

use Dto\Dto;
use Illuminate\Support\Str;

/**
 * Class Transporter
 *
 * @author Johannes Schobel <johannes.schobel@googlemail.com>
 */
abstract class Transporter extends Dto
{

    /**
     * Override the __GET function in order to directly return the "raw value" (e.g., the containing string) of a field
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        // first, check if the field exists, otherwise return null (like the default laravel behavior)
        if (! $this->exists($name)) {
            return null;
        }

        $field = parent::__get($name);
        $type = $field->getStorageType();

        $value = call_user_func([$field, 'to' . Str::ucfirst($type)]);

        return $value;
    }

    /**
     * This method mimics the $request->input() method but works on the "decoded" values
     *
     * @param $key
     * @param $default
     *
     * @return mixed
     */
    public function getInputByKey($key = null, $default = null)
    {
        return array_get($this->toArray(), $key, $default);
    }

    /**
     * Sanitizes the data of a request. This is a superior version of php built-in array_filter() as it preserves
     * FALSE and NULL values as well.
     *
     * @param array $fields a list of fields to be checked in the Dot-Notation (e.g., ['data.name', 'data.description'])
     *
     * @return array an array containing the values if the field was present in the request and the intersection array
     */
    public function sanitizeInput(array $fields)
    {
        // get all request data
        $data = $this->toArray();

        $search = [];
        foreach ($fields as $field) {
            // create a multidimensional array based on $fields
            // which was submitted as DOT notation (e.g., data.attributes.name)
            array_set($search, $field, true);
        }

        // check, if the keys exist in both arrays
        $data = $this->recursive_array_intersect_key(
            $data,
            $search
        );

        return $data;
    }

    /**
     * Recursively intersects 2 arrays based on their keys.
     *
     * @param array $a first array (that keeps the values)
     * @param array $b second array to be compared with
     *
     * @return array an array containing all keys that are present in $a and $b. Only values from $a are returned
     */
    private function recursive_array_intersect_key(array $a, array $b)
    {
        $a = array_intersect_key($a, $b);

        foreach ($a as $key => &$value) {
            if (is_array($value) && is_array($b[$key])) {
                $value = $this->recursive_array_intersect_key($value, $b[$key]);
            }
        }

        return $a;
    }
}