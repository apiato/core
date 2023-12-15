<?php

namespace Apiato\Core\Abstracts\Repositories;

use Apiato\Core\Traits\HasRequestCriteriaTrait;
use Illuminate\Support\Facades\Request;
use Prettus\Repository\Contracts\CacheableInterface as PrettusCacheable;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository as PrettusRepository;
use Prettus\Repository\Traits\CacheableRepository as PrettusCacheableRepository;

abstract class Repository extends PrettusRepository implements PrettusCacheable
{
    use HasRequestCriteriaTrait;
    use PrettusCacheableRepository {
        PrettusCacheableRepository::paginate as cacheablePaginate;
    }

    /**
     * Define the maximum number of entries per page that is returned.
     * Set to 0 to "disable" this feature.
     */
    protected int $maxPaginationLimit = 0;

    protected null|bool $allowDisablePagination = null;

    /**
     * This function relies on strict conventions:
     *    - Repository name should be same as it's model name (model: Foo -> repository: FooRepository).
     *    - If the container contains Models with names different from the container name, the repository class must
     *      implement model() method and return the FQCN e.g., Role::class
     */
    public function model(): string
    {
        $className = $this->getClassName(); // e.g. UserRepository
        $modelName = $this->getModelName($className); // e.g. User

        return $this->getModelNamespace($modelName);
    }

    public function getClassName(): string
    {
        $fullName = static::class;

        return substr($fullName, strrpos($fullName, '\\') + 1);
    }

    public function getModelName(string $className): string|array
    {
        return str_replace('Repository', '', $className);
    }

    public function getModelNamespace(array|string $modelName): string
    {
        return 'App\\Containers\\' . $this->getCurrentSection() . '\\' . $this->getCurrentContainer() . '\\Models\\' . $modelName;
    }

    public function getCurrentSection(): string
    {
        return explode('\\', static::class)[2];
    }

    public function getCurrentContainer(): string
    {
        return explode('\\', static::class)[3];
    }

    /**
     * Paginate the response.
     *
     * Apply pagination to the response.
     * Use ?limit= to specify the number of entities in the response.
     * The client can request all data (skipping pagination) by applying ?limit=0 to the request, if
     * skipping pagination is allowed.
     *
     * @param null $limit
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
        return $limit ?? Request::get('limit');
    }

    public function wantsToSkipPagination(mixed $limit): bool
    {
        return '0' == $limit;
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

    public function exceedsMaxPaginationLimit(mixed $limit): bool
    {
        return $this->maxPaginationLimit > 0 && $limit > $this->maxPaginationLimit;
    }

    public function addRequestCriteria(array $fieldsToDecode = ['id']): static
    {
        $this->pushCriteria(app(RequestCriteria::class));
        if ($this->shouldDecodeSearch()) {
            $this->decodeSearchQueryString($fieldsToDecode);
        }

        return $this;
    }

    public function removeRequestCriteria(): static
    {
        $this->popCriteria(RequestCriteria::class);

        return $this;
    }
}
