<?php

namespace Apiato\Abstract\Repositories;

use Apiato\Http\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Prettus\Repository\Contracts\CacheableInterface;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Repository\Traits\CacheableRepository;

abstract class Repository extends BaseRepository implements CacheableInterface
{
    use CacheableRepository {
        CacheableRepository::paginate as cacheablePaginate;
    }

    /**
     * Define the maximum number of entries per page that is returned.
     * Set to 0 to "disable" this feature.
     */
    protected int $maxPaginationLimit = 0;

    protected bool|null $allowDisablePagination = null;

    public function boot(): void
    {
        parent::boot();

        if ($this->shouldEagerLoadIncludes()) {
            $this->eagerLoadRequestedIncludes();
        }
    }

    /**
     * Enable or disable eager loading of relations requested by the client via "include" query parameter.
     */
    public function shouldEagerLoadIncludes(): bool
    {
        return false;
    }

    /**
     * Eager load relations if requested by the client via "include" query parameter.
     * This is a workaround for incompatible third-party packages. (Fractal, L5Repo).
     *
     * @see https://apiato.atlassian.net/browse/API-905
     */
    public function eagerLoadRequestedIncludes(): void
    {
        $this->scopeQuery(function (Builder|Model $model) {
            if (request()?->has(config('fractal.auto_includes.request_key'))) {
                $validIncludes = [];
                // TODO: Do we need to do the same for the excludes?
                // TODO: Or default includes! Are they eager loaded by default?
                // TODO: What if the include has parameters? e.g. include=books:limit(5|3)
                foreach (Response::getRequestedIncludes() as $includeName) {
                    $relationParts = explode('.', $includeName);
                    $camelCasedIncludeName = $this->filterInvalidRelations($this->model, $relationParts);
                    if ($camelCasedIncludeName) {
                        $validIncludes[] = $camelCasedIncludeName;
                    }
                }

                return $model->with($validIncludes);
            }

            return $model;
        });
    }

    public function filterInvalidRelations(Builder|Model $model, array $relationParts): string|null
    {
        if ([] === $relationParts) {
            return null;
        }

        $relation = $this->figureOutRelationName(array_shift($relationParts));

        if (!method_exists($model, $relation)) {
            return null;
        }

        $nextModel = $model->$relation()->getRelated();

        if ([] === $relationParts) {
            return $relation;
        }

        $nextRelation = $this->filterInvalidRelations($nextModel, $relationParts);

        if (is_null($nextRelation)) {
            return null;
        }

        return $relation . '.' . $nextRelation;
    }

    public function figureOutRelationName(string $includeName): string
    {
        return Str::of($includeName)
            ->replace('-', ' ')
            ->replace('_', ' ')
            ->title()
            ->replace(' ', '')
            ->camel();
    }

    public function model(): string
    {
        return apiato()->repository()->resolveModelName(static::class);
    }

    /**
     * Paginate the response.
     *
     * Apply pagination to the response.
     * Use ?limit= to specify the number of entities in the response.
     * The client can request all data (skipping pagination) by applying ?limit=0 to the request, if
     * skipping pagination is allowed.
     *
     * @param array $columns
     * @param string $method
     */
    public function paginate($limit = null, $columns = ['*'], $method = 'paginate'): mixed
    {
        $limit = $this->setPaginationLimit($limit);

        if ($this->wantsToSkipPagination($limit) && $this->canSkipPagination()) {
            return $this->all($columns);
        }

        if ($this->exceedsMaxPaginationLimit($limit)) {
            $limit = $this->maxPaginationLimit;
        }

        return $this->cacheablePaginate($limit, $columns, $method);
    }

    public function setPaginationLimit($limit): mixed
    {
        // the priority is for the function parameter, if not available then take
        // it from the request if available and if not keep it null.
        return $limit ?? request()?->input('limit');
    }

    public function wantsToSkipPagination(string|int|null $limit): bool
    {
        return '0' === $limit || 0 === $limit;
    }

    public function canSkipPagination(): mixed
    {
        // check local (per repository) rule
        if (!is_null($this->allowDisablePagination)) {
            return $this->allowDisablePagination;
        }

        // check global (.env) rule
        return config('repository.pagination.skip');
    }

    // TODO: rename this method or maybe keep the name but dont return null.
    // Returning null causes multiple if() guard clauses as you can see

    public function exceedsMaxPaginationLimit(mixed $limit): bool
    {
        return $this->maxPaginationLimit > 0 && $limit > $this->maxPaginationLimit;
    }

    /**
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
        return config('apiato.hash-id') && $this->isSearching(request()->query());
    }

    private function isSearching(array $query): bool
    {
        return array_key_exists('search', $query) && $query['search'];
    }

    public function decodeSearchQueryString(array $fieldsToDecode): void
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

    private function decodeValue(string $searchQuery): string|int|null
    {
        $searchValue = $this->parserSearchValue($searchQuery);

        if (is_string($searchValue)) {
            return hashids()->tryDecode($searchValue) ?? $searchValue;
        }

        return null;
    }

    private function parserSearchValue($search)
    {
        if (strpos((string) $search, ';') || strpos((string) $search, ':')) {
            $values = explode(';', (string) $search);
            foreach ($values as $value) {
                $s = explode(':', $value);
                if (1 === count($s)) {
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
                $searchArray[$field] = hashids()->decode($searchArray[$field]);
            }
        }

        return $searchArray;
    }

    private function parserSearchData($search): array
    {
        $searchData = [];

        if (strpos((string) $search, ':')) {
            $fields = explode(';', (string) $search);

            foreach ($fields as $row) {
                try {
                    [$field, $value] = explode(':', $row);
                    $searchData[$field] = $value;
                } catch (\Exception) {
                    // Surround offset error
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
        for ($i = 0; $i < $length; ++$i) {
            $field = $fields[$i];
            $decodedSearchQuery .= "{$field}:$decodedSearchArray[$field]";
            if (1 !== $length && $i < $length - 1) {
                $decodedSearchQuery .= ';';
            }
        }

        return $decodedSearchQuery;
    }

    public function removeRequestCriteria(): static
    {
        $this->popCriteria(RequestCriteria::class);

        return $this;
    }

    private function hashIdEnabled(): bool
    {
        return config('apiato.hash-id');
    }
}
