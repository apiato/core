<?php

namespace Tests\Unit\Abstract\Repositories;

use Apiato\Abstract\Repositories\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Workbench\App\Containers\Identity\User\Data\Repositories\UserRepository;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\MySection\Book\Models\Book;

describe(class_basename(Repository::class), function (): void {
    beforeEach(function (): void {
        config()->set('fractal.auto_includes.request_key', 'include');
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
                    ->has(Book::factory()->count(3)),
                'children',
            )->has(Book::factory()->count(3))
            ->createOne();
        $repository = new class(app()) extends UserRepository {
            public function shouldEagerLoadIncludes(): bool
            {
                return true;
            }
        };

        $result = $repository->all();

        $result->each(function (User $user) use ($userMustLoadRelations, $booksMustLoadRelations, $mustNotLoadRelations): void {
            foreach ($userMustLoadRelations as $relation) {
                $this->assertTrue($user->relationLoaded($relation));
            }
            foreach ($booksMustLoadRelations as $relation) {
                $user->books->each(function (Book $book) use ($relation): void {
                    $this->assertTrue($book->relationLoaded($relation));
                });
            }
            foreach ($mustNotLoadRelations as $relation) {
                $this->assertFalse($user->relationLoaded($relation));
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
    // testMultipleEagerLoadAppliesAllEagerLoads
    it('can eager load multiple includes', function (): void {
        User::factory()
            ->has(
                User::factory()
                    ->has(Book::factory()->count(3)),
                'children',
            )->has(Book::factory()->count(3))
            ->createOne();
        $repository = new class(app()) extends UserRepository {
            public function shouldEagerLoadIncludes(): bool
            {
                return true;
            }
        };

        /** @var Collection<int, User> $result */
        $result = $repository->with('books')->with('children.books')->all();

        $result->each(function (User $user): void {
            $this->assertTrue($user->relationLoaded('books'));
            $this->assertTrue($user->relationLoaded('children'));
            foreach ($user->children as $child) {
                $this->assertTrue($child->relationLoaded('books'));
            }
        });
    });

    it('can cache', function (): void {
        config()->set('repository.cache.enabled', true);
        config()->set('repository.cache.minutes', 1);
        //        config()->set('cache.default', 'database');
        //        User::factory()->create()->transformWith()->toArray();
        $user = User::factory()->createOne();
        $repository = $this->app->make(UserRepository::class);
        /** @var User $cachedUser */
        $cachedUser = $repository->find($user->id);
        DB::table('cache')->get()->dump();

        $this->assertEquals($cachedUser->name, $repository->find($user->id)->name);
        $this->assertEquals($cachedUser->name, $repository->find($user->id)->name);
        $cachedUser->update(['name' => 'new name']);
        $this->assertEquals($cachedUser->name, $repository->find($user->id)->name);
    })->todo();
})->covers(Repository::class);
