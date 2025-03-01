<?php

use Apiato\Core\Repositories\Repository;
use Illuminate\Support\Collection;
use Pest\Expectation;
use Workbench\App\Containers\Identity\User\Data\Repositories\UserRepository;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\MySection\Book\Models\Book;

describe(class_basename(Repository::class), function (): void {
    it('can eager load single relation include', function (
        string $include,
        array $userMustLoadRelations,
        array $booksMustLoadRelations,
        array $mustNotLoadRelations,
    ): void {
        config(['fractal.auto_includes.request_key' => 'include']);
        request()->merge(['include' => $include]);
        User::factory()
            ->has(
                User::factory()
                    ->has(Book::factory(3)),
                'children',
            )->has(Book::factory(3))
            ->createOne();
        $repository = new class extends UserRepository {
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
})->covers(Repository::class);
