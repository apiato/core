<?php

namespace Apiato\Core\Traits;

use Apiato\Core\Exceptions\CoreInternalErrorException;
use Exception;
use Hashids\HashidsException;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Vinkla\Hashids\Facades\Hashids;

trait HasRequestCriteriaTrait
{
    /**
     * @param array $fieldsToDecode
     * @return HasRequestCriteriaTrait
     * @throws RepositoryException
     */
    public function addRequestCriteria(array $fieldsToDecode = ['id']): static
    {
        $this->pushCriteria(app(RequestCriteria::class));
        if ($this->shouldDecodeSearch()) {
            $this->decodeSearchQueryString($fieldsToDecode);
        }

        return $this;
    }

    private function shouldDecodeSearch(): bool
    {
        return $this->hashIdEnabled() && $this->isSearching(request()->query());
    }

    private function hashIdEnabled(): bool
    {
        return config('apiato.hash-id');
    }

    private function isSearching(array $query): bool
    {
        return array_key_exists('search', $query) && $query['search'];
    }

    private function decodeSearchQueryString(array $fieldsToDecode): void
    {
        $query = request()->query();
        $searchQuery = $query['search'];

        $decodedValue = $this->decodeValue($searchQuery);
        $decodedData = $this->decodeData($fieldsToDecode, $searchQuery);

        $decodedQuery = $this->arrayToSearchQuery($decodedData);

        if ($decodedValue) {
            if (empty($decodedQuery)) {
                $decodedQuery .= $decodedValue;
            } else {
                $decodedQuery .= (';' . $decodedValue);
            }
        }

        $query['search'] = $decodedQuery;

        request()->query->replace($query);
    }

    private function decodeValue(string $searchQuery): ?string
    {
        $searchValue = $this->parserSearchValue($searchQuery);

        if ($searchValue) {
            $decodedId = Hashids::decode($searchValue);
            if ($decodedId) {
                return $decodedId[0];
            }
        }

        return $searchValue;
    }

    private function parserSearchValue($search)
    {
        if (strpos($search, ';') || strpos($search, ':')) {
            $values = explode(';', $search);
            foreach ($values as $value) {
                $s = explode(':', $value);
                if (count($s) === 1) {
                    return $s[0];
                }
            }

            return null;
        }

        return $search;
    }

    private function decodeData(array $fieldsToDecode, string $searchQuery): array
    {
        $searchArray = $this->parserSearchData($searchQuery);

        foreach ($fieldsToDecode as $field) {
            if (array_key_exists($field, $searchArray)) {
                if (empty(Hashids::decode($searchArray[$field]))) {
                    throw new HashidsException("Only hash ids are allowed. $field:$searchArray[$field]");
                }
                $searchArray[$field] = Hashids::decode($searchArray[$field])[0];
            }
        }

        return $searchArray;
    }

    private function parserSearchData($search): array
    {
        $searchData = [];

        if (strpos($search, ':')) {
            $fields = explode(';', $search);

            foreach ($fields as $row) {
                try {
                    [$field, $value] = explode(':', $row);
                    $searchData[$field] = $value;
                } catch (Exception $e) {
                    //Surround offset error
                }
            }
        }

        return $searchData;
    }

    private function arrayToSearchQuery(array $decodedSearchArray): string
    {
        $decodedSearchQuery = '';

        $fields = array_keys($decodedSearchArray);
        $length = count($fields);
        for ($i = 0; $i < $length; $i++) {
            $field = $fields[$i];
            $decodedSearchQuery .= "$field:$decodedSearchArray[$field]";
            if ($length !== 1 && $i < $length - 1) {
                $decodedSearchQuery .= ';';
            }
        }

        return $decodedSearchQuery;
    }

    /**
     * @throws CoreInternalErrorException
     */
    public function removeRequestCriteria(): static
    {
        $this->popCriteria(RequestCriteria::class);

        return $this;
    }
}
