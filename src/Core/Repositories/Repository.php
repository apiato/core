<?php

declare(strict_types=1);

namespace Apiato\Core\Repositories;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use Apiato\Http\RequestRelation;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CacheableInterface;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Repository\Traits\CacheableRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * @template TModel of Model
 */
abstract class Repository extends BaseRepository implements CacheableInterface
{
    use CacheableRepository;

    /**
     * Define the maximum number of entries per page that is returned.
     * Set to 0 to "disable" this feature.
     */
    protected int $maxPaginationLimit = 0;

    protected null|bool $allowDisablePagination = null;

    /** @var \Closure[] */
    protected array $scopes = [];

    public function __construct()
    {
        parent::__construct(app());
    }

    #[\Override]
    public function boot(): void
    {
        parent::boot();

        if ($this->shouldEagerLoadIncludes()) {
            $this->eagerLoadRequestedIncludes(app(RequestRelation::class));
        }
    }

    /**
     * Enable or disable eager loading of relations requested by the client via "include" query parameter.
     */
    public function shouldEagerLoadIncludes(): bool
    {
        return true;
    }

    /**
     * Eager load relations if requested by the client via "include" query parameter.
     * This is a workaround for incompatible third-party packages. (Fractal, L5Repo).
     *
     * TODO: What if the include has parameters? e.g. include=books:limit(5|3). Does this still work?
     *
     * @see https://apiato.atlassian.net/browse/API-905
     */
    public function eagerLoadRequestedIncludes(RequestRelation $requestRelation): void
    {
        $this->scope(static function (Builder|Model $model) use ($requestRelation): Builder|Model {
            if ($requestRelation->requestingIncludes()) {
                if ($model instanceof Model) {
                    return $model->with($requestRelation->getValidRelationsFor($model));
                }

                return $model->with($requestRelation->getValidRelationsFor($model->getModel()));
            }

            return $model;
        });
    }

    /**
     * Add a new global scope to the model.
     */
    public function scope(\Closure $scope): static
    {
        $this->scopes[] = $scope;

        return $this;
    }

    /**
     * Returns the current Model instance.
     * *
     * @return TModel
     */
    public function getModel()
    {
        return parent::getModel();
    }

    /**
     * Retrieve all data of repository, paginated.
     *
     * @param int|null $limit   The number of entries per page. If set to 0, the pagination is disabled.
     * @param array    $columns
     * @param string   $method
     *
     * @throws RepositoryException
     */
    #[\Override]
    public function paginate($limit = null, $columns = ['*'], $method = 'paginate'): mixed
    {
        $limit = $this->setPaginationLimit($limit);

        if ($this->wantsToSkipPagination($limit) && $this->canSkipPagination()) {
            return $this->all($columns);
        }

        if ($this->exceedsMaxPaginationLimit($limit)) {
            $limit = $this->maxPaginationLimit;
        }

        if (!$this->allowedCache('paginate') || $this->isSkippedCache()) {
            return parent::paginate($limit, $columns, $method);
        }

        $key = $this->getCacheKey('paginate', \func_get_args());

        $time = $this->getCacheTime();
        $value = $this->getCacheRepository()->remember($key, $time, function () use ($limit, $columns, $method) {
            return parent::paginate($limit, $columns, $method);
        });

        $this->resetModel();
        $this->resetScope();

        return $value;
    }

    public function setPaginationLimit(null|int|string $limit = null): int
    {
        // The priority is for the function parameter, if not available then take it
        // from the request if available and if not keep it null.
        return (int)($limit ?? request()?->input('limit', 0));
    }

    public function wantsToSkipPagination(int $limit): bool
    {
        return $limit === 0;
    }

    public function canSkipPagination(): mixed
    {
        // Check local (per repository) rule
        if (!\is_null($this->allowDisablePagination)) {
            return $this->allowDisablePagination;
        }

        // Check global (.env) rule
        return config('repository.pagination.skip');
    }

    /**
     * Retrieve all data of repository.
     *
     * @param array $columns
     *
     * @return Collection<array-key, TModel>
     *
     * @throws RepositoryException
     */
    public function all($columns = ['*'])
    {
        if (!$this->allowedCache('all') || $this->isSkippedCache()) {
            return parent::all($columns);
        }

        $key = $this->getCacheKey('all', \func_get_args());
        $time = $this->getCacheTime();
        $value = $this->getCacheRepository()->remember($key, $time, function () use ($columns) {
            return parent::all($columns);
        });

        $this->resetModel();
        $this->resetScope();

        return $value;
    }

    public function resetScope(): static
    {
        parent::resetScope();

        $this->resetScopes();

        return $this;
    }

    public function resetScopes(): static
    {
        $this->scopes = [];

        return $this;
    }

    public function exceedsMaxPaginationLimit(mixed $limit): bool
    {
        return $this->maxPaginationLimit > 0 && $limit > $this->maxPaginationLimit;
    }

    /**
     * @throws RepositoryException
     */
    public function addRequestCriteria(): static
    {
        if ($this->shouldDecodeSearch()) {
            $this->decodeSearchQueryString();
        }

        $this->pushCriteria(app(RequestCriteria::class));

        return $this;
    }

    public function decodeSearchQueryString(): void
    {
        /** @var Request $request */
        $request = app(Request::class);
        $query = $request->query();
        $searchKey = config('repository.criteria.params.search', 'search');
        $searchQuery = $query[$searchKey];

        if (\is_string($searchQuery) === false || $searchQuery === '') {
            return;
        }

        $searchData = $this->parserSearchData($searchQuery);
        $decodedData = $this->getDecodedSearchValues($searchData);

        if ($decodedData === $searchData) {
            return;
        }

        $newSearchQuery = $this->arrayToSearchQuery($decodedData);

        $query[$searchKey] = $newSearchQuery;

        $request->query->replace($query);
    }

    public function removeRequestCriteria(): static
    {
        $this->popCriteria(RequestCriteria::class);

        return $this;
    }

    /**
     * Create a new Model instance.
     *
     * @return TModel
     */
    public function make(array $attributes)
    {
        return $this->getModel()->newInstance($attributes);
    }

    /**
     * Persist an entity with the given attributes.
     *
     * @return TModel
     *
     * @throws ResourceCreationFailed
     */
    public function store(Arrayable|array $data)
    {
        if (\is_array($data)) {
            return $this->create($data);
        }

        return $this->create($data->toArray());
    }

    /**
     * Save a new model and return the instance.
     *
     * @return TModel
     *
     * @throws ResourceCreationFailed
     */
    public function create(array $attributes)
    {
        try {
            return parent::create($attributes);
        } catch (\Throwable) {
            throw ResourceCreationFailed::create(class_basename($this->model()));
        }
    }

    public function model(): string
    {
        return apiato()->repository()->resolveModelName(static::class);
    }

    /**
     * Persist an entity instance to the database.
     *
     * @param TModel $model
     *
     * @return TModel
     */
    public function save($model)
    {
        $model->save();

        return $model;
    }

    /**
     * Get the first record matching the attributes. If the record is not found, create it.
     *
     * @return TModel
     */
    public function firstOrCreate(array $attributes = [], array $values = [])
    {
        /** @var TModel $model */
        $model = parent::firstOrCreate($attributes);

        if ($model->wasRecentlyCreated) {
            $model->update($values);
        }

        return $model;
    }

    /**
     * Update an entity in repository by id.
     *
     * @param int|string $id
     *
     * @return TModel
     *
     * @throws ResourceNotFound
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        try {
            return parent::update($attributes, $id);
        } catch (ModelNotFoundException) {
            throw ResourceNotFound::create(class_basename($this->model()));
        }
    }

    /**
     * Find an entity by its primary key or throw an exception.
     *
     * @return TModel
     *
     * @throws ResourceNotFound
     */
    public function findOrFail(int|string $id, array $columns = ['*'])
    {
        return $this->find($id, $columns) ?? throw ResourceNotFound::create(class_basename($this->model()));
    }

    /**
     * Find an entity/s by its primary key.
     *
     * @param int|string|array|Arrayable $id
     * @param array                      $columns
     *
     * @return ($id is array|Arrayable ? Collection<array-key, TModel> : TModel|null)
     *
     * @throws RepositoryException
     */
    public function find($id, $columns = ['*'])
    {
        try {
            if (!$this->allowedCache('find') || $this->isSkippedCache()) {
                return parent::find($id, $columns);
            }

            $key = $this->getCacheKey('find', \func_get_args());
            $time = $this->getCacheTime();
            $value = $this->getCacheRepository()->remember($key, $time, function () use ($id, $columns) {
                return parent::find($id, $columns);
            });

            $this->resetModel();
            $this->resetScope();

            return $value;
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    /**
     * Find an entity by its primary key.
     *
     * @return TModel|null
     */
    public function findById(int|string $id, array $columns = ['*'])
    {
        return $this->find($id, $columns);
    }

    /**
     * Find multiple models by their primary keys.
     *
     * @return Collection<array-key, TModel>
     */
    public function findMany(array|Arrayable $ids, array $columns = ['*'])
    {
        return $this->find($ids, $columns) ?? new Collection();
    }

    /**
     * Find data by field and value.
     *
     * @param (\Closure(static): mixed)|string|array|Expression $field
     * @param array                                             $columns
     *
     * @return Collection<array-key, TModel>
     */
    public function findByField($field, $value = null, $columns = ['*'])
    {
        if (!$this->allowedCache('findByField') || $this->isSkippedCache()) {
            return parent::findByField($field, $value, $columns);
        }

        $key = $this->getCacheKey('findByField', \func_get_args());
        $time = $this->getCacheTime();
        $value = $this->getCacheRepository()->remember($key, $time, function () use ($field, $value, $columns) {
            return parent::findByField($field, $value, $columns);
        });

        $this->resetModel();
        $this->resetScope();

        return $value;
    }

    /**
     * Find models by multiple fields.
     *
     * @param array|string $columns
     *
     * @return Collection<array-key, TModel>
     */
    public function findWhere(array $where, $columns = ['*'])
    {
        if (!$this->allowedCache('findWhere') || $this->isSkippedCache()) {
            return parent::findWhere($where, $columns);
        }

        $key = $this->getCacheKey('findWhere', \func_get_args());
        $time = $this->getCacheTime();
        $value = $this->getCacheRepository()->remember($key, $time, function () use ($where, $columns) {
            return parent::findWhere($where, $columns);
        });

        $this->resetModel();
        $this->resetScope();

        return $value;
    }

    /**
     * Delete the model from the database.
     *
     * @param int|string $id
     *
     * @throws ResourceNotFound
     */
    public function delete($id): bool
    {
        try {
            return (bool) parent::delete($id);
        } catch (ModelNotFoundException) {
            throw ResourceNotFound::create(class_basename($this->model()));
        }
    }

    /**
     * @param class-string<CriteriaInterface> $criteria Criteria class name
     * @param array<string, mixed>            $args     Arguments to pass to the criteria constructor
     *
     * @throws RepositoryException
     * @throws BindingResolutionException
     */
    public function pushCriteriaWith(string $criteria, array $args): static
    {
        /** @var CriteriaInterface $criteriaInstance */
        $criteriaInstance = $this->app->makeWith($criteria, $args);

        return $this->pushCriteria($criteriaInstance);
    }

    protected function applyScope(): static
    {
        parent::applyScope();

        $this->applyScopes();

        return $this;
    }

    protected function applyScopes(): static
    {
        foreach ($this->scopes as $scope) {
            if (!\is_callable($scope)) {
                throw new \RuntimeException('Query scope is not callable');
            }

            $this->model = $scope($this->model);
        }

        return $this;
    }

    private function parserSearchData(string $search): array
    {
        $searchData = [];

        if (str_contains($search, ':') === false) {
            return $searchData;
        }

        $fields = explode(';', $search);

        foreach ($fields as $field) {
            if (str_contains($field, ':') === false) {
                continue;
            }

            $parts = explode(':', $field, 2);

            if (\count($parts) !== 2) {
                continue;
            }

            $field = trim($parts[0]);

            if ($field === '') {
                continue;
            }

            $searchData[$field] = trim($parts[1]);
        }

        return $searchData;
    }

    private function getDecodedSearchValues(array $searchData): array
    {
        foreach ($searchData as $field => $value) {
            $isBool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($isBool !== null) {
                $searchData[$field] = $value;
                continue;
            }

            if (is_numeric($value)) {
                $searchData[$field] = $value;
                continue;
            }

            $decodedId = hashids()->decode($value);

            if ($decodedId === null) {
                $searchData[$field] = $value;
                continue;
            }

            $searchData[$field] = $decodedId;
        }

        return $searchData;
    }

    private function shouldDecodeSearch(): bool
    {
        return config('apiato.hash-id') && $this->isSearching();
    }

    private function isSearching(): bool
    {
        $searchKey = config('repository.criteria.params.search', 'search');
        /** @var Request $request */
        $request = app(Request::class);

        return $request->filled($searchKey);
    }

    /**
     * Reconstructs the search string from an array of field => value pairs.
     */
    private function arrayToSearchQuery(array $decodedSearchArray): string
    {
        $decodedSearchQuery = '';

        $fields = array_keys($decodedSearchArray);
        $length = \count($fields);
        foreach ($fields as $i => $iValue) {
            $field = $iValue;
            $decodedSearchQuery .= \sprintf('%s:%s', $field, $decodedSearchArray[$field]);

            if ($length !== 1 && $i < $length - 1) {
                $decodedSearchQuery .= ';';
            }
        }

        return $decodedSearchQuery;
    }
}
