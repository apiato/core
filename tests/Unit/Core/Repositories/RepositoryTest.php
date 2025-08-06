<?php

namespace Tests\Unit\Abstract\Repositories;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use Apiato\Core\Repositories\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Pest\Expectation;
use Prettus\Repository\Criteria\RequestCriteria;
use Workbench\App\Containers\Identity\User\Data\Repositories\UserRepository;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\MySection\Book\Data\Repositories\BookRepository;
use Workbench\App\Containers\MySection\Book\Models\Book;

describe(class_basename(Repository::class), function (): void {
    it('can add/remove request criteria', function (): void {
        $repository = new BookRepository();

        $result = $repository->addRequestCriteria();

        expect($result->getCriteria())->toHaveCount(1)
            ->and($result->getCriteria()->first())->toBeInstanceOf(RequestCriteria::class);

        $result = $repository->removeRequestCriteria();

        expect($result->getCriteria())->toBeEmpty();
    });

    it('can push criteria and passing args', function (): void {
        $repository = new BookRepository();

        $result = $repository->pushCriteriaWith(RequestCriteria::class, ['criteria' => request()]);

        expect($result->getCriteria())->toHaveCount(1)
            ->and($result->getCriteria()->first())->toBeInstanceOf(RequestCriteria::class);
    });

    it('discover its model', function (): void {
        $repository = new BookRepository();

        expect($repository->model())->toBe(Book::class);
    });

    it('can cache method call results', function (string $method, \Closure $args): void {
        Cache::clear();
        config(['repository.cache.enabled' => true]);
        $user = User::factory()->createOne();
        $repository = $this->app->make(UserRepository::class);
        $arguments = $args($user->id);
        $cacheKey = $repository->getCacheKey($method, [$arguments]);

        expect(Cache::missing($cacheKey))->toBeTrue();

        // hit the cache
        $repository->$method($arguments);

        expect(Cache::has($cacheKey))->toBeTrue();
    })->with([
        'all' => [
            'all',
            static fn () => ['*'],
        ],
        'paginate' => [
            'paginate',
            static fn () => null,
        ],
        'find' => [
            'find',
            static fn ($id) => $id,
        ],
        'findByField' => [
            'findByField',
            static fn ($id) => ['id', $id],
        ],
        'findWhere' => [
            'findWhere',
            static fn ($id) => [$id],
        ],
    ]);

    describe('scopes', function (): void {
        it('can push/reset scopes stack', function (): void {
            $repository = new class extends UserRepository {
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

        $result = $repository->make(['title' => 'test']);

        expect($result)->toBeInstanceOf(Book::class)
            ->and($result->title)->toBe('test');
    });

    it('can get the model instance', function (): void {
        $repository = new BookRepository();

        $result = $repository->getModel();

        expect($result)->toBeInstanceOf(Book::class);
    });

    it('can store a new model instance', function ($data): void {
        $repository = new BookRepository();

        $result = $repository->store($data);

        expect($result)->toBeInstanceOf(Book::class)
            ->and($result->title)->toBe('test');
    })->with([
        'array' => [
            fn () => ['title' => 'test'],
        ],
        'model' => [
            fn () => Book::factory()->makeOne(['title' => 'test']),
        ],
    ]);

    describe('create method', function (): void {
        it('can create a new model instance', function (): void {
            $repository = new BookRepository();

            $result = $repository->create(['title' => 'test']);

            expect($result)->toBeInstanceOf(Book::class)
                ->and($result->title)->toBe('test');
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
        $book = Book::factory()->makeOne();

        $this->assertModelMissing($book);

        $repository->save($book);

        $this->assertModelExists($book);
    });

    describe('first or create', function (): void {
        it('can find first or create', function (): void {
            $repository = new BookRepository();
            $book = Book::factory()->makeOne();

            $this->assertModelMissing($book);

            $result = $repository->firstOrCreate(['title' => $book->title], $book->toArray());

            expect($result)->toBeInstanceOf(Book::class)
                ->and($result->title)->toBe($book->title);

            $this->assertModelExists($result);
        });

        it('can update attributes if model is found', function (): void {
            $repository = new UserRepository();

            $result = $repository->firstOrCreate(
                [
                    'email' => 'saruman@the.white',
                    'password' => 'password',
                ],
                [
                    'name' => 'saruman',
                ],
            );

            expect($result)->toBeInstanceOf(User::class)
                ->and($result->name)->toBe('saruman')
                ->and($result->email)->toBe('saruman@the.white');
        });
    });

    describe('update', function (): void {
        it('can update an entity instance', function (): void {
            $repository = new BookRepository();
            $book = Book::factory()->createOne();

            $result = $repository->update(['title' => 'updated'], $book->id);

            $this->assertModelExists($result);
            expect($result->title)->toBe('updated');
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
            $book = Book::factory()->createOne();

            $result = $repository->findOrFail($book->id);

            expect($result)->toBeInstanceOf(Book::class)
                ->and($result->id)->toBe($book->id);
        });

        it('throws custom exception', function (int|null $id): void {
            expect(function () use ($id): void {
                $repository = new BookRepository();

                $repository->findOrFail($id);
            })->toThrow(ResourceNotFound::create('Book'));
        })->with([
            777,
            null
        ]);
    });

    describe('find', function (): void {
        it('can find an entity instance', function (): void {
            $repository = new BookRepository();
            $book = Book::factory()->createOne();

            $result = $repository->find($book->id);

            expect($result)->toBeInstanceOf(Book::class)
                ->and($result->id)->toBe($book->id);
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
            $book = Book::factory()->createOne();

            $result = $repository->findById($book->id);

            expect($result)->toBeInstanceOf(Book::class)
                ->and($result->id)->toBe($book->id);
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
            $book = Book::factory()->createOne();

            $result = $repository->delete($book->id);

            expect($result)->toBeTrue();
            $this->assertModelMissing($book);
        });

        it('throws custom exception', function (): void {
            expect(function (): void {
                $repository = new BookRepository();

                $repository->findOrFail(777);
            })->toThrow(ResourceNotFound::create('Book'));
        });
    });
})->covers(Repository::class);
