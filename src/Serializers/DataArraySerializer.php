<?php

namespace Apiato\Core\Serializers;

use Illuminate\Support\Facades\Request;
use League\Fractal\Serializer\ArraySerializer;

class DataArraySerializer extends ArraySerializer
{
    public function collection(string|null $resourceKey, array $data): array
    {
        return $this->filterResponse($data);
    }

    private function filterResponse(array $data): array
    {
        if ($requestFilters = Request::get('filter')) {
            return ['data' => $this->filterResponseRecursively($data, explode(';', $requestFilters))];
        }

        return compact('data');
    }

    private function filterResponseRecursively(array $responseArray, array $filters): array
    {
        foreach ($responseArray as $key => $value) {
            if (in_array($key, $filters, true)) {
                // we have found our element - so continue with the next one
                continue;
            }

            if (is_array($value)) {
                // it is an array - so go one step deeper
                $value = $this->filterResponseRecursively($value, $filters);
                $responseArray[$key] = $value;
            } elseif (!in_array($key, $filters, true)) {
                unset($responseArray[$key]);
            }
        }

        return $responseArray;
    }

    public function item(string|null $resourceKey, array $data): array
    {
        return $this->filterResponse($data);
    }

    public function null(): array|null
    {
        return ['data' => []];
    }
}
