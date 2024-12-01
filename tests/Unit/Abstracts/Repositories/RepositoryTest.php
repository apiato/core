<?php

namespace Apiato\Core\Tests\Unit\Abstracts\Repositories;

use Apiato\Core\Abstracts\Repositories\Repository;
use Apiato\Core\Tests\Infrastructure\Doubles\Book;
use Apiato\Core\Tests\Infrastructure\Doubles\BookFactory;
use Apiato\Core\Tests\Infrastructure\Doubles\User;
use Apiato\Core\Tests\Infrastructure\Doubles\UserFactory;
use Apiato\Core\Tests\Infrastructure\Doubles\UserRepository;
use Apiato\Core\Tests\Unit\UnitTestCase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Repository::class)]
final class RepositoryTest extends UnitTestCase
{
    public static function includeDataProvider(): array
    {
        return [
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
        ];
    }

    #[DataProvider('includeDataProvider')]
    public function testEagerLoadSingleRelationRequestedViaRequest(
        string $include,
        array $userMustLoadRelations,
        array $booksMustLoadRelations,
        array $mustNotLoadRelations,
    ): void {
        request()->merge(compact('include'));
        UserFactory::new()
            ->has(
                UserFactory::new()
                ->has(BookFactory::new()->count(3)),
                'children',
            )->has(BookFactory::new()->count(3))
            ->createOne();
        $repository = new class(app()) extends UserRepository {
            public function includesEagerLoadingEnabled(): bool
            {
                return true;
            }
        };

        $result = $repository->all();

        $result->each(function (User $user) use ($userMustLoadRelations, $booksMustLoadRelations, $mustNotLoadRelations) {
            foreach ($userMustLoadRelations as $relation) {
                $this->assertTrue($user->relationLoaded($relation));
            }
            foreach ($booksMustLoadRelations as $relation) {
                $user->books->each(function (Book $book) use ($relation) {
                    $this->assertTrue($book->relationLoaded($relation));
                });
            }
            foreach ($mustNotLoadRelations as $relation) {
                $this->assertFalse($user->relationLoaded($relation));
            }
        });
    }

    public function testMultipleEagerLoadAppliesAllEagerLoads(): void
    {
        UserFactory::new()
            ->has(
                UserFactory::new()
                ->has(BookFactory::new()->count(3)),
                'children',
            )->has(BookFactory::new()->count(3))
            ->createOne();
        $repository = new class(app()) extends UserRepository {
            public function includesEagerLoadingEnabled(): bool
            {
                return true;
            }
        };

        /** @var Collection<int, User> $result */
        $result = $repository->with('books')->with('children.books')->all();

        $result->each(function (User $user) {
            $this->assertTrue($user->relationLoaded('books'));
            $this->assertTrue($user->relationLoaded('children'));
            foreach ($user->children as $child) {
                $this->assertTrue($child->relationLoaded('books'));
            }
        });
    }

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('fractal.auto_includes.request_key', 'include');
    }
}
