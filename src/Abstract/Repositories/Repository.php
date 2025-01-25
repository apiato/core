<?php

namespace Apiato\Abstract\Repositories;

use Apiato\Foundation\Support\Traits\HasRequestCriteria;
use Apiato\Support\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Prettus\Repository\Contracts\CacheableInterface;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Traits\CacheableRepository;

abstract class Repository extends BaseRepository implements CacheableInterface
{
    use HasRequestCriteria;
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

    // TODO: rename this method or maybe keep the name but dont return null.
    // Returning null causes multiple if() guard clauses as you can see
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
}
