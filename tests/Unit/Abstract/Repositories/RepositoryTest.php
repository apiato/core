<?php

namespace Tests\Unit\Abstract\Repositories;

use Apiato\Abstract\Repositories\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Pest\Expectation;
use Workbench\App\Containers\Identity\User\Data\Repositories\UserRepository;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\MySection\Book\Data\Repositories\BookRepository;
use Workbench\App\Containers\MySection\Book\Models\Book;

describe(class_basename(Repository::class), function (): void {
    beforeEach(function (): void {
        config(['fractal.auto_includes.request_key' => 'include']);
    });

    it('can eager load single relation include', function (
        string $include,
        array $userMustLoadRelations,
        array $booksMustLoadRelations,
        array $mustNotLoadRelations,
    ): void {
        request()->merge(['include' => $include]);
        User::factory()
            ->has(
                User::factory()
                    ->has(Book::factory(3)),
                'children',
            )->has(Book::factory(3))
            ->createOne();
        $repository = new class(app()) extends UserRepository {
            public function shouldEagerLoadIncludes(): bool
            {
                return true;
            }
        };

        $result = $repository->all();

        expect($result)->toBeInstanceOf(Collection::class)
            ->each(function (Expectation $expectation) use ($userMustLoadRelations, $booksMustLoadRelations, $mustNotLoadRelations): void {
                foreach ($userMustLoadRelations as $relation) {
                    $expectation->relationLoaded($relation)->toBeTrue();
                }
                foreach ($booksMustLoadRelations as $relation) {
                    $expectation->books->each(function (Expectation $expectation) use ($relation): void {
                        $expectation->relationLoaded($relation)->toBeTrue();
                    });
                }
                foreach ($mustNotLoadRelations as $relation) {
                    $expectation->relationLoaded($relation)->toBeFalse();
                }
            });
    })->with([
        'single relation' => [
            'books',
            ['books'],
            [],
            ['children', 'parent'],
        ],
        'works with duplicate include' => [
            'books,books',
            ['books'],
            [],
            ['children', 'parent'],
        ],
        'multiple relations' => [
            'books,children',
            ['books', 'children'],
            [],
            ['parent'],
        ],
        'single nested relation' => [
            'books.author',
            ['books'],
            ['author'],
            ['children', 'parent'],
        ],
        'multiple nested relations' => [
            'books.author.children,children.parent',
            ['books', 'children'],
            ['author'],
            ['parent'],
        ],
        'multiple and single nested relations' => [
            'parent,books.author',
            ['parent', 'books'],
            ['author'],
            ['children'],
        ],
    ]);

    it('can eager load multiple includes', function (): void {
        User::factory()
            ->has(
                User::factory()
                    ->has(Book::factory(3)),
                'children',
            )->has(Book::factory(3))
            ->createOne();
        $repository = new class(app()) extends UserRepository {
            public function shouldEagerLoadIncludes(): bool
            {
                return true;
            }
        };

        /** @var Collection<int, User> $result */
        $result = $repository->with('books')->with('children.books')->all();

        expect($result)->toBeInstanceOf(Collection::class)
            ->each(function (Expectation $expectation): void {
                $expectation->relationLoaded('books')->toBeTrue();
                $expectation->relationLoaded('children')->toBeTrue();
                $expectation->children->each(function (Expectation $expectation): void {
                    $expectation->relationLoaded('books')->toBeTrue();
                });
            });
    });

    it('discover its model', function (): void {
        $repository = new BookRepository(app());

        expect($repository->model())->toBe(Book::class);
    });

    it('can cache', function (): void {
        config(['repository.cache.enabled' => true]);
        $user = User::factory()->createOne();
        $repository = $this->app->make(UserRepository::class);
        $cacheKey = $repository->getCacheKey('find', [$user->id]);

        expect(Cache::missing($cacheKey))->toBeTrue();

        // hit the cache
        $repository->find($user->id);

        expect(Cache::has($cacheKey))->toBeTrue();
    });
})->covers(Repository::class);
