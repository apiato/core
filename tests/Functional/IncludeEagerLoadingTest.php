<?php

use Apiato\Core\Repositories\Repository;
use Apiato\Core\Transformers\Transformer;
use Apiato\Http\Response;
use Workbench\App\Containers\Identity\User\Data\Repositories\UserRepository;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\MySection\Book\Models\Book;

describe('Include eager loading', function (): void {
    it('can eager load requested includes', function (): void {
        $user = User::factory()
            ->has(
                User::factory()->has(Book::factory(3)),
                'children',
            )->has(Book::factory(3))
            ->createOne();
        request()->merge([
            'include' => 'children.books,parent',
        ]);
        $repository = new class extends UserRepository {
            public function shouldEagerLoadIncludes(): bool
            {
                return true;
            }
        };

        expect($user->relationLoaded('books'))->toBeFalse()
            ->and($user->relationLoaded('parent'))->toBeFalse()
            ->and($user->children->first()->relationLoaded('books'))->toBeFalse();

        $result = $repository
            ->with('comments')
            ->findById($user->id);

        // TODO
        //        dd(LazyLoadingViolationException::class);
        // eager true false test

        expect($result->relationLoaded('children'))->toBeTrue()
            ->and($result->relationLoaded('parent'))->toBeTrue()
            ->and($result->relationLoaded('comments'))->toBeTrue()
            ->and($result->children->first()->relationLoaded('books'))->toBeTrue();
    });
})->covers(Response::class, Transformer::class, Repository::class);
