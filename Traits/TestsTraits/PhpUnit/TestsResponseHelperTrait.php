<?php

namespace Apiato\Core\Traits\TestsTraits\PhpUnit;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JsonException;

trait TestsResponseHelperTrait
{
    public function assertResponseContainKeys($keys): void
    {
        if (!is_array($keys)) {
            $keys = (array)$keys;
        }

        $arrayResponse = $this->removeDataKeyFromResponse($this->getResponseContentArray());

        foreach ($keys as $key) {
            $this->assertTrue(array_key_exists($key, $arrayResponse));
        }
    }

    /**
     * @param array $responseContent
     *
     * @return mixed
     */
    private function removeDataKeyFromResponse(array $responseContent): mixed
    {
        if (array_key_exists('data', $responseContent)) {
            return $responseContent['data'];
        }

        return $responseContent;
    }

    public function assertResponseContainValues($values): void
    {
        if (!is_array($values)) {
            $values = (array)$values;
        }

        $arrayResponse = $this->removeDataKeyFromResponse($this->getResponseContentArray());

        foreach ($values as $value) {
            $this->assertTrue(in_array($value, $arrayResponse));
        }
    }

    public function assertResponseContainKeyValue($data): void
    {
        // `responseContentToArray` will remove the `data` node
        $httpResponse = json_encode(Arr::sortRecursive((array)$this->getResponseContentArray()));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $expected = $this->formatToExpectedJson($key, $value);
            $this->assertTrue(
                Str::contains($httpResponse, $expected),
                "The JSON fragment [ $expected ] does not exist in the response [ $httpResponse ]."
            );
        }
    }

    private function formatToExpectedJson($key, $value): string
    {
        $expected = json_encode([$key => $value]);

        if (Str::startsWith($expected, '{')) {
            $expected = substr($expected, 1);
        }

        if (Str::endsWith($expected, '}')) {
            $expected = substr($expected, 0, -1);
        }

        return trim($expected);
    }

    /**
     * @throws JsonException
     */
    public function assertValidationErrorContain(array $messages): void
    {
        $responseContent = $this->getResponseContentObject();

        foreach ($messages as $key => $value) {
            $this->assertEquals($responseContent->errors->{$key}[0], $value);
        }
    }
}
