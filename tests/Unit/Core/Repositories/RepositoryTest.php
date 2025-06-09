<?php

declare(strict_types=1);

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use Apiato\Core\Repositories\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Pest\Expectation;
use Prettus\Repository\Criteria\RequestCriteria;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Workbench\App\Containers\Identity\User\Data\Repositories\UserRepository;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\MySection\Book\Data\Repositories\BookRepository;
use Workbench\App\Containers\MySection\Book\Models\Book;

describe(class_basename(Repository::class), function (): void {
    describe('RequestCriteria handling', function (): void {
        it('can add request criteria', function (): void {
            $repository = new BookRepository();

            $bookRepository = $repository->addRequestCriteria();

            expect($bookRepository->getCriteria())->toHaveCount(1)
                ->and($bookRepository->getCriteria()->first())->toBeInstanceOf(RequestCriteria::class);
        });

        it('can remove request criteria', function (): void {
            $repository = new BookRepository();
            $repository->addRequestCriteria();

            $bookRepository = $repository->removeRequestCriteria();

            expect($bookRepository->getCriteria())->toBeEmpty();
        });

        it('decodes search query when hash-id is enabled', function (): void {
            config(['apiato.hash-id' => true]);

            $hashedId = hashids()->encode(123);
            $searchString = 'id:' . $hashedId;
            $expectedString = 'id:123';

            $request = Request::create('/', SymfonyRequest::METHOD_GET, ['search' => $searchString]);
            app()->instance(Request::class, $request);

            $repository = new BookRepository();
            $repository->addRequestCriteria();

            $actualQuery = app(Request::class)->query('search');

            expect($actualQuery)->toBe($expectedString);
        });

        it('does not decode search query when hash-id is disabled', function (): void {
            config(['apiato.hash-id' => false]);

            $hashedId = hashids()->encode(123);
            $searchString = 'id:' . $hashedId;

            $request = Request::create('/', SymfonyRequest::METHOD_GET, ['search' => $searchString]);
            app()->instance(Request::class, $request);

            $repository = new BookRepository();
            $repository->addRequestCriteria();

            $actualQuery = app(Request::class)->query('search');

            expect($actualQuery)->toBe($searchString);
        });

        it('ignores boolean values when decoding search query', function (): void {
            config(['apiato.hash-id' => true]);

            $searchString = 'is_active:true;is_admin:false;status:1;flag:0';

            $request = Request::create('/', SymfonyRequest::METHOD_GET, ['search' => $searchString]);
            app()->instance(Request::class, $request);

            $repository = new BookRepository();
            $repository->addRequestCriteria();

            $actualQuery = app(Request::class)->query('search');

            expect($actualQuery)->toBe($searchString);
        });

        it('ignores numeric values when decoding search query', function (): void {
            config(['apiato.hash-id' => true]);

            $searchString = 'age:30;count:5';

            $request = Request::create('/', SymfonyRequest::METHOD_GET, ['search' => $searchString]);
            app()->instance(Request::class, $request);

            $repository = new BookRepository();
            $repository->addRequestCriteria();

            $actualQuery = app(Request::class)->query('search');

            expect($actualQuery)->toBe($searchString);
        });

        it('handles complex search input with mixed values', function (): void {
            config(['apiato.hash-id' => true]);

            $hashedId1 = hashids()->encode(123);
            $hashedId2 = hashids()->encode(456);
            $searchString = sprintf('id:%s;name:John;is_active:true;role_id:%s;age:30', $hashedId1, $hashedId2);
            $expectedString = 'id:123;name:John;is_active:true;role_id:456;age:30';

            $request = Request::create('/', SymfonyRequest::METHOD_GET, ['search' => $searchString]);
            app()->instance(Request::class, $request);

            $repository = new BookRepository();
            $repository->addRequestCriteria();

            $actualQuery = app(Request::class)->query('search');

            expect($actualQuery)->toBe($expectedString);
        });

        it('does nothing when search parameter is empty', function (): void {
            config(['apiato.hash-id' => true]);

            $request = Request::create('/', SymfonyRequest::METHOD_GET, ['search' => '']);
            app()->instance(Request::class, $request);

            $repository = new BookRepository();
            $repository->addRequestCriteria();

            $actualQuery = app(Request::class)->query('search');

            expect($actualQuery)->toBe('');
        });

        it('does nothing when search parameter is not a string', function (): void {
            config(['apiato.hash-id' => true]);

            $request = Request::create('/', SymfonyRequest::METHOD_GET, ['search' => null]);
            app()->instance(Request::class, $request);

            $repository = new BookRepository();
            $repository->addRequestCriteria();

            $actualQuery = app(Request::class)->query('search');

            expect($actualQuery)->toBeNull();
        });
    });

    it('can push criteria and passing args', function (): void {
        $repository = new BookRepository();

        $bookRepository = $repository->pushCriteriaWith(RequestCriteria::class, ['criteria' => app(Request::class)]);

        expect($bookRepository->getCriteria())->toHaveCount(1)
            ->and($bookRepository->getCriteria()->first())->toBeInstanceOf(RequestCriteria::class);
    });

    it('discover its model', function (): void {
        $repository = new BookRepository();

        expect($repository->model())->toBe(Book::class);
    });

    it('can cache method call results', function (string $method, Closure $args): void {
        Cache::clear();
        config(['repository.cache.enabled' => true]);
        $model = User::factory()->createOne();
        $repository = $this->app->make(UserRepository::class);
        $arguments = $args($model->id);
        $cacheKey = $repository->getCacheKey($method, [$arguments]);

        expect(Cache::missing($cacheKey))->toBeTrue();

        // hit the cache
        $repository->{$method}($arguments);

        expect(Cache::has($cacheKey))->toBeTrue();
    })->with([
        'all' => [
            'all',
            static fn (): array => ['*'],
        ],
        'paginate' => [
            'paginate',
            static fn (): int => 0,
        ],
        'find' => [
            'find',
            static fn ($id) => $id,
        ],
        'findByField' => [
            'findByField',
            static fn ($id): array => ['id', $id],
        ],
        'findWhere' => [
            'findWhere',
            static fn ($id): array => [$id],
        ],
    ]);

    describe('scopes', function (): void {
        it('can push/reset scopes stack', function (): void {
            $repository = new class () extends UserRepository {
                public function shouldEagerLoadIncludes(): bool
                {
                    return false;
                }

                public function getScopes(): array
                {
                    return $this->scopes;
                }
            };

            $repository
                ->scope(static fn ($query) => $query)
                ->scope(static fn ($query) => $query);

            expect($repository->getScopes())->toHaveCount(2);

            $repository->resetScope();

            expect($repository->getScopes())->toHaveCount(0);
        });

        it('can apply scopes', function (): void {
            $repository = new UserRepository();
            User::factory()->createOne();
            User::factory()->createOne([
                'name' => 'saruman',
            ]);
            User::factory()->createOne();
            User::factory()->createOne([
                'email' => 'saruman@example.com',
            ]);

            $repository->scope(static fn ($query) => $query->where('name', 'saruman'))
                ->scope(static fn ($query) => $query->orWhere('email', 'saruman@example.com'));

            $result = $repository->all();

            expect($result)->toHaveCount(2)
                ->and($result)->first()->name->toBe('saruman')
                ->and($result)->last()->email->toBe('saruman@example.com');
        });
    });

    it('can make new model instance', function (): void {
        $repository = new BookRepository();

        $book = $repository->make(['title' => 'test']);

        expect($book)->toBeInstanceOf(Book::class)
            ->and($book->title)->toBe('test');
    });

    it('can get the model instance', function (): void {
        $repository = new BookRepository();

        $book = $repository->getModel();

        expect($book)->toBeInstanceOf(Book::class);
    });

    it('can store a new model instance', function ($data): void {
        $repository = new BookRepository();

        $book = $repository->store($data);

        expect($book)->toBeInstanceOf(Book::class)
            ->and($book->title)->toBe('test');
    })->with([
        'array' => [
            fn (): array => ['title' => 'test'],
        ],
        'model' => [
            fn () => Book::factory()->makeOne(['title' => 'test']),
        ],
    ]);

    describe('create method', function (): void {
        it('can create a new model instance', function (): void {
            $repository = new BookRepository();

            $book = $repository->create(['title' => 'test']);

            expect($book)->toBeInstanceOf(Book::class)
                ->and($book->title)->toBe('test');
        });

        it('throws custom exception', function (): void {
            expect(function (): void {
                $repository = new BookRepository();

                $repository->create(['id' => 'test']);
            })->toThrow(ResourceCreationFailed::create('Book'));
        });
    });

    it('can save the model instance', function (): void {
        $repository = new BookRepository();
        $model = Book::factory()->makeOne();

        $this->assertModelMissing($model);

        $repository->save($model);

        $this->assertModelExists($model);
    });

    describe('first or create', function (): void {
        it('can find first or create', function (): void {
            $repository = new BookRepository();
            $model = Book::factory()->makeOne();

            $this->assertModelMissing($model);

            $book = $repository->firstOrCreate(['title' => $model->title], $model->toArray());

            expect($book)->toBeInstanceOf(Book::class)
                ->and($book->title)->toBe($model->title);

            $this->assertModelExists($book);
        });

        it('can update attributes if model is found', function (): void {
            $repository = new UserRepository();

            $model = $repository->firstOrCreate(
                [
                    'email'    => 'saruman@the.white',
                    'password' => 'password',
                ],
                [
                    'name' => 'saruman',
                ],
            );

            expect($model)->toBeInstanceOf(User::class)
                ->and($model->name)->toBe('saruman')
                ->and($model->email)->toBe('saruman@the.white');
        });
    });

    describe('update', function (): void {
        it('can update an entity instance', function (): void {
            $repository = new BookRepository();
            $model = Book::factory()->createOne();

            $book = $repository->update(['title' => 'updated'], $model->id);

            $this->assertModelExists($book);
            expect($book->title)->toBe('updated');
        });

        it('throws custom exception', function (): void {
            expect(function (): void {
                $repository = new BookRepository();

                $repository->update(['id' => 'test'], 777);
            })->toThrow(ResourceNotFound::create('Book'));
        });
    });

    describe('findOrFail', function (): void {
        it('can find an entity instance', function (): void {
            $repository = new BookRepository();
            $model = Book::factory()->createOne();

            $book = $repository->findOrFail($model->id);

            expect($book)->toBeInstanceOf(Book::class)
                ->and($book->id)->toBe($model->id);
        });

        it('throws custom exception', function (): void {
            expect(function (): void {
                $repository = new BookRepository();

                $repository->findOrFail(777);
            })->toThrow(ResourceNotFound::create('Book'));
        });
    });

    describe('find', function (): void {
        it('can find an entity instance', function (): void {
            $repository = new BookRepository();
            $model = Book::factory()->createOne();

            $result = $repository->find($model->id);

            expect($result)->toBeInstanceOf(Book::class)
                ->and($result?->getKey())->toBe($model->id);
        });

        it('returns null if model is not found', function (): void {
            $repository = new BookRepository();

            $result = $repository->find(777);

            expect($result)->toBeNull();
        });
    });

    describe('findById', function (): void {
        it('can find an entity instance', function (): void {
            $repository = new BookRepository();
            $model = Book::factory()->createOne();

            $result = $repository->findById($model->id);

            expect($result)->toBeInstanceOf(Book::class)
                ->and($result?->getKey())->toBe($model->id);
        });

        it('returns null if model is not found', function (): void {
            $repository = new BookRepository();

            $result = $repository->findById(777);

            expect($result)->toBeNull();
        });
    });

    describe('findMany', function (): void {
        it('can find multiple model instances', function (): void {
            $repository = new BookRepository();
            $books = Book::factory(3)->create();

            $result = $repository->findMany($books->pluck('id')->toArray());

            expect($result)->toBeInstanceOf(Collection::class)
                ->toHaveCount(3)
                ->and($result)->each(function (Expectation $expectation): void {
                    $expectation->toBeInstanceOf(Book::class);
                });
        });

        it('returns empty collection if no models are found', function (): void {
            $repository = new BookRepository();

            $result = $repository->findMany([777, 888]);

            expect($result)->toBeInstanceOf(Collection::class)
                ->toBeEmpty();
        });
    });

    describe('findByField', function (): void {
        it('can find model instances', function (): void {
            $repository = new BookRepository();
            $books = Book::factory(3)->create();
            $id = $books->offsetGet(1)->id;

            $result = $repository->findByField('id', $id);

            expect($result)->toBeInstanceOf(Collection::class)
                ->toHaveCount(1)
                ->and($result->first()->id)->toBe($id);
        });

        it('returns empty collection if no models are found', function (): void {
            $repository = new BookRepository();

            $result = $repository->findByField('id', 777);

            expect($result)->toBeInstanceOf(Collection::class)
                ->toBeEmpty();
        });
    });

    describe('findWhere', function (): void {
        it('can find model instances', function (): void {
            $repository = new BookRepository();
            $books = Book::factory(3)->create();
            $id = $books->offsetGet(1)->id;

            $result = $repository->findWhere(['id' => $id]);

            expect($result)->toBeInstanceOf(Collection::class)
                ->toHaveCount(1)
                ->and($result->first()->id)->toBe($id);
        });

        it('returns empty collection if no models are found', function (): void {
            $repository = new BookRepository();

            $result = $repository->findWhere(['id' => 777]);

            expect($result)->toBeInstanceOf(Collection::class)
                ->toBeEmpty();
        });
    });

    describe('delete', function (): void {
        it('can delete an entity instance', function (): void {
            $repository = new BookRepository();
            $model = Book::factory()->createOne();

            $result = $repository->delete($model->id);

            expect($result)->toBeTrue();
            $this->assertModelMissing($model);
        });

        it('throws custom exception', function (): void {
            expect(function (): void {
                $repository = new BookRepository();

                $repository->findOrFail(777);
            })->toThrow(ResourceNotFound::create('Book'));
        });

        it('throws exception when deleting non-existent entity', function (): void {
            expect(function (): void {
                $repository = new BookRepository();

                $repository->delete(777);
            })->toThrow(Error::class);
        });
    });

    describe('pagination handling', function (): void {
        it('can set pagination limit from request', function (): void {
            $repository = new BookRepository();

            $request = Request::create('/', SymfonyRequest::METHOD_GET, ['limit' => '42']);
            app()->instance(Request::class, $request);

            $limit = $repository->setPaginationLimit(42);

            expect($limit)->toBe(42);
        });

        it('can set pagination limit from parameter', function (): void {
            $repository = new BookRepository();

            $limit = $repository->setPaginationLimit(42);

            expect($limit)->toBe(42);
        });

        it('can check if pagination should be skipped', function (): void {
            $repository = new BookRepository();

            expect($repository->wantsToSkipPagination(0))->toBeTrue();
            expect($repository->wantsToSkipPagination(5))->toBeFalse();
        });

        it('checks if disable pagination is allowed via repository property', function (): void {
            $repository = mock(UserRepository::class)->makePartial();
            $repository->shouldReceive('canSkipPagination')->andReturn(true);

            expect($repository->canSkipPagination())->toBeTrue();

            $repository = mock(UserRepository::class)->makePartial();
            $repository->shouldReceive('canSkipPagination')->andReturn(false);

            expect($repository->canSkipPagination())->toBeFalse();
        });

        it('checks if disable pagination is allowed via global config', function (): void {
            $repository = new BookRepository();

            config(['repository.pagination.skip' => true]);
            expect($repository->canSkipPagination())->toBeTrue();

            config(['repository.pagination.skip' => false]);
            expect($repository->canSkipPagination())->toBeFalse();
        });

        it('can check if limit exceeds max pagination limit', function (): void {
            $legacyMock = mock(BookRepository::class)->makePartial();
            $legacyMock->allows('exceedsMaxPaginationLimit')->andReturnUsing(
                fn ($limit): bool => $limit > 10
            );

            expect($legacyMock->exceedsMaxPaginationLimit(20))->toBeTrue();
            expect($legacyMock->exceedsMaxPaginationLimit(5))->toBeFalse();
        });
    });
})->covers(Repository::class);
