<?php

namespace Apiato\Core\Traits;

use Apiato\Core\Abstracts\Requests\Request;
use Apiato\Core\Abstracts\Transporters\Transporter;
use App\Ship\Exceptions\InternalErrorException;
use Illuminate\Support\Arr;

/**
 * Class SanitizerTrait.
 *
 * @author  Mahmoud Zalt <mahmoud@zalt.me>
 * @author  Johannes Schobel <johannes.schobel@googlemail.com>
 */
trait SanitizerTrait
{

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
        $data = $this->getData();

        $results = [];
        foreach (Arr::dot($data) as $key => $value) {
            foreach ($fields as $field) {
                $pattern = str_replace(['.', '*'], ['\.', '([^\.]+)'], $field);
                if (preg_match('/^' . $pattern . '/', $key)) {
                    Arr::set($results, $key, $value);
                }
            }
        }

        return $results;
    }

    /**
     * @return array
     * @throws InternalErrorException
     */
    private function getData()
    {
        // get all request data
        if ($this instanceof Transporter) {
            $data = $this->toArray();
        } elseif ($this instanceof Request) {
            $data = $this->all();
        } else {
            throw new InternalErrorException('Unsupported class type for sanitization.');
        }

        return $data;
    }

}
