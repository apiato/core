<?php

namespace Apiato\Core\Traits;

use Apiato\Core\Exceptions\CoreInternalErrorException;
use Apiato\Core\Exceptions\IncorrectIdException;
use Vinkla\Hashids\Facades\Hashids;

trait HashIdTrait
{
    /**
     * endpoint to be skipped from decoding their ID's (example for external ID's).
     */
    private array $skippedEndpoints = [
        // 'orders/{id}/external',
    ];

    /**
     * TODO: BC: This method is only used on Models and should be moved there
     * Hashes the value of a field (e.g., ID).
     *
     * Will be used by the Eloquent Models (since it's used as trait there).
     *
     * @param string|null $field The field of the model to be hashed
     */
    public function getHashedKey(string|null $field = null): string|null
    {
        // if no key is set, use the default key name (i.e., id)
        if (null === $field) {
            $field = $this->getKeyName();
        }

        // hash the ID only if hash-id enabled in the config
        if (config('apiato.hash-id')) {
            // we need to get the VALUE for this KEY (model field)
            $value = $this->getAttribute($field);

            if (null === $value) {
                return null;
            }

            return $this->encoder($value);
        }

        return $this->getAttribute($field);
    }

    public function encoder($id): string
    {
        return Hashids::encode($id);
    }

    public function encode($id): string
    {
        return $this->encoder($id);
    }

    public function decodeArray(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $result[] = $this->decode($id);
        }

        return $result;
    }

    /**
     * @return int|null
     *
     * if the decoded id is bigger than PHP_INT_MAX, the decoder will return a string
     * we will cut that off from propagating, because such big numerical identifiers
     * are not practically used
     *
     * if the id is not decodable, null will be returned
     */
    public function decode(string|null $id): int|null
    {
        // check if passed as null, (could be an optional decodable variable)
        if (null === $id || 'null' === strtolower($id)) {
            return $id;
        }

        // do the decoding if the ID looks like a hashed one
        $decoded = $this->decoder($id);
        if (!empty($decoded)) {
            return $decoded[0];
        }

        return null;
    }

    private function decoder($id): array
    {
        return Hashids::decode($id);
    }

    /**
     * without decoding the encoded ID's you won't be able to use
     * validation features like `exists:table,id`.
     *
     * @throws IncorrectIdException
     * @throws \Throwable
     */
    protected function decodeHashedIdsBeforeValidation(array $requestData): array
    {
        // the hash ID feature must be enabled to use this decoder feature.
        if (!empty($this->decode) && config('apiato.hash-id')) {
            // iterate over each key (ID that needs to be decoded) and call keys locator to decode them
            foreach ($this->decode as $key) {
                $requestData = $this->locateAndDecodeIds($requestData, $key);
            }
        }

        return $requestData;
    }

    /**
     * Search the IDs to be decoded in the request data.
     *
     * @throws IncorrectIdException
     * @throws \Throwable
     */
    private function locateAndDecodeIds($requestData, $key): mixed
    {
        // split the key based on the "."
        $fields = explode('.', $key);

        // loop through all elements of the key.
        return $this->processField($requestData, $fields, $key);
    }

    /**
     * Recursive function to process (decode) the request data with a given key.
     *
     * @throws IncorrectIdException
     * @throws \Throwable
     */
    private function processField($data, $keysTodo, $currentFieldName): mixed
    {
        // is the current field a null?! we can give it back and chill out
        if (is_null($data)) {
            return $data;
        }

        // check if there are no more fields to be processed
        if (empty($keysTodo)) {
            // there are no more keys left - so basically we need to decode this entry
            if ($this->skipHashIdDecode($data)) {
                return $data;
            }

            throw_if(
                !is_string($data),
                (new CoreInternalErrorException('String expected, got ' . gettype($data), 422)),
            );

            $decodedField = $this->decode($data);

            if (is_null($decodedField)) {
                throw new IncorrectIdException('ID (' . $currentFieldName . ') is incorrect, consider using the hashed ID.');
            }

            return $decodedField;
        }

        // take the first element from the field
        $field = array_shift($keysTodo);

        // is the current field an array?! we need to process it like crazy
        if ('*' === $field) {
            // make sure field value is an array
            $data = is_array($data) ? $data : [$data];

            // process each field of the array (and go down one level!)
            $fields = $data;
            foreach ($fields as $key => $value) {
                $data[$key] = $this->processField($value, $keysTodo, $currentFieldName . '[' . $key . ']');
            }

            return $data;
        }

        // check if the key we are looking for does, in fact, really exist
        if (!array_key_exists($field, $data)) {
            return $data;
        }

        // go down one level
        $value = $data[$field];
        $data[$field] = $this->processField($value, $keysTodo, $field);

        return $data;
    }

    public function skipHashIdDecode($field): bool
    {
        return empty($field);
    }
}
