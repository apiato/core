<?php

namespace Apiato\Core\Abstracts\Transporters;

use Apiato\Core\Abstracts\Requests\Request;
use Apiato\Core\Traits\SanitizerTrait;
use Dto\Dto;
use Dto\RegulatorInterface;
use Illuminate\Support\Str;

/**
 * Class Transporter
 *
 * @author  Johannes Schobel <johannes.schobel@googlemail.com>
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
abstract class Transporter extends Dto
{

    use SanitizerTrait;

    /**
     * Holds instances of objects.
     *
     * @var  array
     */
    private $instances = [];

    /**
     * Override the Dto constructor to extend it for supporting Requests objects as $input.
     *
     * Transporter constructor.
     *
     * @param null                         $input
     * @param null                         $schema
     * @param \Dto\RegulatorInterface|null $regulator
     */
    public function __construct($input = null, $schema = null, RegulatorInterface $regulator = null)
    {
        // if the transporter got a Request object, get the content and headers
        // and pass them as array input to the Dto constructor..
        if ($input instanceof Request) {
            $content = $input->toArray();
            $heders = ['_headers' => $input->headers->all()];

            $input = array_merge($heders, $content);
        }

        parent::__construct($input, $schema, $regulator);
    }

    /**
     * This method mimics the $request->input() method but works on the "decoded" values
     *
     * @param null $key
     * @param null $default
     *
     * @return  mixed
     */
    public function getInputByKey($key = null, $default = null)
    {
        return array_get($this->toArray(), $key, $default);
    }

    /**
     * Since passing Objects doesn't work, because they cannot be hydrated by the DTO.
     * This gives us the ability to pass instances, via the DTO.
     */
    public function setInstance($key, $value)
    {
        $this->instances[$key] = $value;
    }

    /**
     * Override the __GET function to gain more control and flexibility in Apiato, and modify the default behavior
     * of the parent function.
     *
     * @param $name
     *
     * @return  mixed|null
     */
    public function __get($name)
    {

        // if set as instance, return it directly
        if(isset($this->instances[$name])){
            return $this->instances[$name];
        }

        // if the field does not exist, return null instance of throwing exception `InvalidKeyException` by the parent.
        if (!$this->exists($name)) {
            return null;
        }

        $field = parent::__get($name);

        $type = $field->getStorageType();
        $value = call_user_func([$field, 'to' . Str::ucfirst($type)]);

        return $value;
    }

}
