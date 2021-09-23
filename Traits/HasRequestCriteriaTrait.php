<?php

namespace Apiato\Core\Traits;

use Apiato\Core\Abstracts\Repositories\Repository;
use Apiato\Core\Exceptions\CoreInternalErrorException;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Vinkla\Hashids\Facades\Hashids;

trait HasRequestCriteriaTrait
{
    /**
     * @throws CoreInternalErrorException
     * @throws RepositoryException
     */
    public function addRequestCriteria($repository = null, array $fieldsToDecode = ['id']): self
    {
        $validatedRepository = $this->validateRepository($repository);
        $validatedRepository->pushCriteria(app(RequestCriteria::class));
        $this->decodeSearchQueryString($fieldsToDecode);
        return $this;
    }

    /**
     * Validates, if the given Repository exists or uses $this->repository on the Task/Action to apply functions
     *
     * @param $repository
     *
     * @return Repository
     *
     * @throws CoreInternalErrorException
     */
    private function validateRepository($repository): Repository
    {
        $validatedRepository = $repository;

        // check if we have a "custom" repository
        if (null === $repository) {
            if (!isset($this->repository)) {
                throw new CoreInternalErrorException('No protected or public accessible repository available');
            }
            $validatedRepository = $this->repository;
        }

        // check, if the validated repository is null
        if (null === $validatedRepository) {
            throw new CoreInternalErrorException();
        }

        // check if it is a Repository class
        if (!($validatedRepository instanceof Repository)) {
            throw new CoreInternalErrorException();
        }

        return $validatedRepository;
    }

    private function decodeSearchQueryString(array $fieldsToDecode): void
    {
        if (!$this->hashIdEnabled()) {
            return;
        }

        $query = request()->query();

        if (array_key_exists('search', $query)) {
            $query['search'] = $this->decodeSearchFields($fieldsToDecode, $query['search']);

            request()->query->replace($query);
        }
    }

    private function hashIdEnabled(): bool
    {
        return config('apiato.hash-id');
    }

    private function decodeSearchFields(array $fieldsToDecode, string $searchQuery): string
    {
        $searchArray = $this->searchQueryToArray($searchQuery);
        foreach ($fieldsToDecode as $field) {
            if (array_key_exists($field, $searchArray)) {
                $searchArray[$field] = Hashids::decode($searchArray[$field])[0];
            }
        }
        return $this->arrayToSearchQuery($searchArray);
    }

    private function searchQueryToArray($search): array
    {
        $searchArray = [];

        $segments = $this->getSearchSegments($search);
        foreach ($segments as $segment) {
            try {
                [$key, $value] = $this->getSearchSegmentKeyValues($segment);
                $searchArray[$key] = $value;
            } catch (\Exception $e) {
                //Surround offset error
            }
        }

        return $searchArray;
    }

    private function getSearchSegments($search): array
    {
        return explode(';', $search);
    }

    private function getSearchSegmentKeyValues($segment): array
    {
        return explode(':', $segment);
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
    public function removeRequestCriteria($repository = null): self
    {
        $validatedRepository = $this->validateRepository($repository);
        $validatedRepository->popCriteria(RequestCriteria::class);
        return $this;
    }
}
