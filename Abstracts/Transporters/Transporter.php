<?php

namespace Apiato\Core\Abstracts\Transporters;

use Apiato\Core\Traits\SanitizerTrait;
use Dto\Dto;
use Illuminate\Support\Str;

/**
 * Class Transporter
 *
 * @author Johannes Schobel <johannes.schobel@googlemail.com>
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
abstract class Transporter extends Dto
{

    use SanitizerTrait;

    /**
     * Override the __GET function in order to directly return the "raw value" (e.g., the containing string) of a field
     *
     * @param $name
     *
     * @return  mixed|null
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

}
