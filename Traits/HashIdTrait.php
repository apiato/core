<?php

namespace Apiato\Core\Traits;

use Apiato\Core\Exceptions\IncorrectIdException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Vinkla\Hashids\Facades\Hashids;
use function is_null;
use function strtolower;


trait HashIdTrait
{
    /**
     * endpoint to be skipped from decoding their ID's (example for external ID's)
     */
    private array $skippedEndpoints = [
//        'orders/{id}/external',
    ];

    /**
     * Hashes the value of a field (e.g., ID)
     *
     * Will be used by the Eloquent Models (since it's used as trait there).
     *
     * @param null $field The field of the model to be hashed
     * @return  mixed
     */
    public function getHashedKey($field = null)
    {
        // if no key is set, use the default key name (i.e., id)
        if ($field === null) {
            $field = $this->getKeyName();
        }

        // hash the ID only if hash-id enabled in the config
        if (Config::get('apiato.hash-id')) {
            // we need to get the VALUE for this KEY (model field)
            $value = $this->getAttribute($field);
            return $this->encoder($value);
        }

        return $this->getAttribute($field);
    }

    public function encoder($id): string
    {
        return Hashids::encode($id);
    }

    public function findKeyAndReturnValue(&$subject, $findKey, $callback)
    {
        // if the value is not an array, then you have reached the deepest point of the branch, so return the value.
        if (!is_array($subject)) {
            return $subject;
        }

        foreach ($subject as $key => $value) {
            if ($key == $findKey && isset($subject[$findKey])) {
                $subject[$key] = $callback($subject[$findKey]);
                break;
            }

            // add the value with the recursive call
            $this->findKeyAndReturnValue($value, $findKey, $callback);
        }
    }

    public function decodeArray(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $result[] = $this->decode($id);
        }

        return $result;
    }

    public function decode($id, $parameter = null)
    {
        // check if passed as null, (could be an optional decodable variable)
        if (is_null($id) || strtolower($id) == 'null') {
            return $id;
        }

        // do the decoding if the ID looks like a hashed one
        return empty($this->decoder($id)) ? [] : $this->decoder($id)[0];
    }

    private function decoder($id): array
    {
        return Hashids::decode($id);
    }

    public function encode($id): string
    {
        return $this->encoder($id);
    }

    /**
     * Automatically decode any found `id` in the URL, no need to be used anymore.
     * Since now the user will define what needs to be decoded in the request.
     *
     * All ID's passed with all endpoints will be decoded before entering the Application
     */
    public function runHashedIdsDecoder(): void
    {
        if (Config::get('apiato.hash-id')) {
            Route::bind('id', function ($id, $route) {
                // skip decoding some endpoints
                if (!in_array($route->uri(), $this->skippedEndpoints)) {
                    // decode the ID in the URL
                    $decoded = $this->decoder($id);

                    if (empty($decoded)) {
                        throw new IncorrectIdException('ID (' . $id . ') is incorrect, consider using the hashed ID
                        instead of the numeric ID.');
                    }

                    return $decoded[0];
                }
            });
        }
    }

    /**
     * without decoding the encoded ID's you won't be able to use
     * validation features like `exists:table,id`
     * @param array $requestData
     * @return array
     */
    protected function decodeHashedIdsBeforeValidation(array $requestData): array
    {
        // the hash ID feature must be enabled to use this decoder feature.
        if (Config::get('apiato.hash-id') && isset($this->decode) && !empty($this->decode)) {
            // iterate over each key (ID that needs to be decoded) and call keys locator to decode them
            foreach ($this->decode as $key) {
                $requestData = $this->locateAndDecodeIds($requestData, $key);
            }
        }

        return $requestData;
    }

    /**
     * Search the IDs to be decoded in the request data
     *
     * @param $requestData
     * @param $key
     *
     * @return  mixed
     */
    private function locateAndDecodeIds($requestData, $key)
    {
        // split the key based on the "."
        $fields = explode('.', $key);
        // loop through all elements of the key.
        $transformedData = $this->processField($requestData, $fields);

        return $transformedData;
    }

    /**
     * Recursive function to process (decode) the request data with a given key
     * @param $data
     * @param $keysTodo
     * @return array|mixed
     */
    private function processField($data, $keysTodo)
    {
        // check if there are no more fields to be processed
        if (empty($keysTodo)) {
            // there are no more keys left - so basically we need to decode this entry
            $decodedId = $this->decode($data);
            return $decodedId;
        }

        // take the first element from the field
        $field = array_shift($keysTodo);

        // is the current field an array?! we need to process it like crazy
        if ($field == '*') {
            //make sure field value is an array
            $data = is_array($data) ? $data : [$data];

            // process each field of the array (and go down one level!)
            $fields = $data;
            foreach ($fields as $key => $value) {
                $data[$key] = $this->processField($value, $keysTodo);
            }
            return $data;

        } else {
            // check if the key we are looking for does, in fact, really exist
            if (!array_key_exists($field, $data)) {
                return $data;
            }

            // go down one level
            $value = $data[$field];
            $data[$field] = $this->processField($value, $keysTodo);
            return $data;
        }
    }
}
