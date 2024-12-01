<?php

namespace Apiato\Core\Abstracts\Repositories;

use Apiato\Core\Traits\CanEagerLoadTrait;
use Apiato\Core\Traits\HasRequestCriteriaTrait;
use Prettus\Repository\Contracts\CacheableInterface;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Traits\CacheableRepository;

abstract class Repository extends BaseRepository implements CacheableInterface
{
    use HasRequestCriteriaTrait;
    use CanEagerLoadTrait;
    use CacheableRepository {
        CacheableRepository::paginate as cacheablePaginate;
    }

    // TODO: BC: set return type to void
    /**
     * Define the maximum number of entries per page that is returned.
     * Set to 0 to "disable" this feature.
     */
    protected int $maxPaginationLimit = 0;

    protected bool|null $allowDisablePagination = null;

    public function boot()
    {
        parent::boot();

        if ($this->includesEagerLoadingEnabled()) {
            $this->eagerLoadRequestedRelations();
        }
    }

    /**
     * Enable or disable eager loading of relations requested by the client via "include" query parameter.
     */
    public function includesEagerLoadingEnabled(): bool
    {
        // TODO: BC: disable it by default for v8 and enable by default for v13
        return false;
    }

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
        return $limit ?? request()?->input('limit');
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
