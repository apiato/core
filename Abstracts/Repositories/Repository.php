<?php

namespace Apiato\Core\Abstracts\Repositories;

use Prettus\Repository\Contracts\CacheableInterface as PrettusCacheable;
use Prettus\Repository\Eloquent\BaseRepository as PrettusRepository;
use Prettus\Repository\Traits\CacheableRepository as PrettusCacheableRepository;
use Request;

abstract class Repository extends PrettusRepository implements PrettusCacheable
{
    use PrettusCacheableRepository {
        PrettusCacheableRepository::paginate as cacheablePaginate;
    }

    /**
     * Define the maximum amount of entries per page that is returned.
     * Set to 0 to "disable" this feature
     */
    protected int $maxPaginationLimit = 0;

    /**
     * Define the maximum amount of entries per page that is returned.
     * Set to 0 to "disable" this feature
     */
    protected ?bool $allowDisablePagination = null;

    /**
     * This function relies on strict conventions:
     *    - Repository name should be same as it's model name (model: Foo -> repository: FooRepository).
     *    - If the container contains Models with names different from the container name, the repository class must
     *      implement model() method and return the FQCN e.g. Role::class
     */
    public function model(): string
    {
        $className = $this->getClassName(); // e.g. UserRepository
        $modelName = $this->getModelName($className); // e.g. User
        return $this->getModelNamespace($modelName);
    }

    private function getClassName(): string
    {
        $fullName = static::class;
        return substr($fullName, strrpos($fullName, '\\') + 1);
    }

    private function getModelName(string $className): string|array
    {
        return str_replace('Repository', '', $className);
    }

    private function getModelNamespace(array|string $modelName): string
    {
        return 'App\\Containers\\' . $this->getCurrentSection() . '\\' . $this->getCurrentContainer() . '\\Models\\' . $modelName;
    }

    private function getCurrentSection(): string
    {
        return explode('\\', static::class)[2];
    }

    private function getCurrentContainer(): string
    {
        return explode('\\', static::class)[3];
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
    }

    /**
     * Paginate the response
     *
     * Apply pagination to the response. Use ?limit= to specify the amount of entities in the response.
     * The client can request all data (skipping pagination) by applying ?limit=0 to the request, if
     * skipping pagination is allowed.
     *
     * @param null $limit
     * @param array $columns
     * @param string $method
     *
     * @return  mixed
     */
    public function paginate($limit = null, $columns = ['*'], $method = "paginate")
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

    private function setPaginationLimit($limit): mixed
    {
        // the priority is for the function parameter, if not available then take
        // it from the request if available and if not keep it null.
        return isset($limit) ? $limit : Request::get('limit');
    }

    private function wantsToSkipPagination(mixed $limit): bool
    {
        return $limit == "0";
    }

    private function canSkipPagination(): mixed
    {
        // check local (per repository) rule
        if (!is_null($this->allowDisablePagination))
            return $this->allowDisablePagination;

        // check global (.env) rule
        return config('repository.pagination.skip');
    }

    private function exceedsMaxPaginationLimit(mixed $limit): bool
    {
        return $this->maxPaginationLimit > 0 && $limit > $this->maxPaginationLimit;
    }
}
