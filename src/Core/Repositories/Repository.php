<?php

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
use Prettus\Repository\Contracts\CacheableInterface;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Events\RepositoryEntityDeleted;
use Prettus\Repository\Events\RepositoryEntityDeleting;
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

    protected bool|null $allowDisablePagination = null;

    /** @var \Closure[] */
    protected array $scopes = [];

    public function __construct()
    {
        parent::__construct(app());
    }

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
        $this->scope(function (Builder|Model $model) use ($requestRelation): Builder|Model {
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
     * @param int|null $limit The number of entries per page. If set to 0, the pagination is disabled.
     * @param array $columns
     * @param string $method
     *
     * @throws RepositoryException
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

        if (!$this->allowedCache('paginate') || $this->isSkippedCache()) {
            return parent::paginate($limit, $columns, $method);
        }

        $key = $this->getCacheKey('paginate', func_get_args());

        $time = $this->getCacheTime();
        $value = $this->getCacheRepository()->remember($key, $time, function () use ($limit, $columns, $method) {
            return parent::paginate($limit, $columns, $method);
        });

        $this->resetModel();
        $this->resetScope();

        return $value;
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

        $key = $this->getCacheKey('all', func_get_args());
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
            return hashids()->decode($searchValue) ?? $searchValue;
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
                $searchArray[$field] = hashids()->decodeOrFail($searchArray[$field]);
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
        if ($data instanceof Arrayable) {
            return $this->create($data->toArray());
        }

        return $this->create($data);
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
        } catch (\Throwable $throwable) {
            throw ResourceCreationFailed::create($this->getExceptionMessage($throwable));
        }
    }

    protected function getExceptionMessage(\Throwable|null $throwable = null): string
    {
        if (!is_null($throwable) && app()->hasDebugModeEnabled()) {
            return $throwable->getMessage();
        }

        return class_basename($this->model());
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
        } catch (ModelNotFoundException $exception) {
            throw ResourceNotFound::create($this->getExceptionMessage($exception));
        }
    }

    /**
     * Find an entity by its primary key.
     *
     * @return TModel|null
     *
     * @throws RepositoryException
     */
    public function findById(int|string $id, array $columns = ['*'])
    {
        return $this->find($id, $columns);
    }

    /**
     * Find an entity/s by its primary key.
     *
     * @param int|string|array|Arrayable $id
     * @param array $columns
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

            $key = $this->getCacheKey('find', func_get_args());
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
     * Find multiple models by their primary keys.
     *
     * @return Collection<array-key, TModel>
     *
     * @throws RepositoryException
     */
    public function findMany(array|Arrayable $ids, array $columns = ['*'])
    {
        return $this->find($ids, $columns) ?? new Collection();
    }

    /**
     * Find data by field and value.
     *
     * @param (\Closure(static): mixed)|string|array|Expression $field
     * @param array $columns
     *
     * @return Collection<array-key, TModel>
     *
     * @throws RepositoryException
     */
    public function findByField($field, $value = null, $columns = ['*'])
    {
        if (!$this->allowedCache('findByField') || $this->isSkippedCache()) {
            return parent::findByField($field, $value, $columns);
        }

        $key = $this->getCacheKey('findByField', func_get_args());
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
     *
     * @throws RepositoryException
     */
    public function findWhere(array $where, $columns = ['*'])
    {
        if (!$this->allowedCache('findWhere') || $this->isSkippedCache()) {
            return parent::findWhere($where, $columns);
        }

        $key = $this->getCacheKey('findWhere', func_get_args());
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
     * @throws RepositoryException
     */
    public function delete($id): bool
    {
        $this->applyScope();

        $temporarySkipPresenter = $this->skipPresenter;
        $this->skipPresenter(true);

        $model = $this->findOrFail($id);
        $originalModel = clone $model;

        $this->skipPresenter($temporarySkipPresenter);
        $this->resetModel();

        event(new RepositoryEntityDeleting($this, $model));

        $deleted = $model->delete();

        event(new RepositoryEntityDeleted($this, $originalModel));

        return (bool) $deleted;
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
            if (!is_callable($scope)) {
                throw new \RuntimeException('Query scope is not callable');
            }
            $this->model = $scope($this->model);
        }

        return $this;
    }

    /**
     * Find an entity by its primary key or throw an exception.
     *
     * @return TModel
     *
     * @throws ResourceNotFound
     * @throws RepositoryException
     */
    public function findOrFail(int|string $id, array $columns = ['*'])
    {
        return $this->find($id, $columns) ?? throw ResourceNotFound::create($this->getExceptionMessage());
    }

    /**
     * @param class-string<CriteriaInterface> $criteria Criteria class name
     * @param array<string, mixed> $args Arguments to pass to the criteria constructor
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
}
