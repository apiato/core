<?php

use Apiato\Core\Repositories\Repository;
use Apiato\Core\Transformers\Transformer;
use Apiato\Http\Response;
use Workbench\App\Containers\Identity\User\Data\Repositories\UserRepository;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\MySection\Book\Models\Book;

describe('Include eager loading', function (): void {
    beforeEach(function (): void {
        $this->user = User::factory()
            ->has(
                User::factory()->has(Book::factory(3)),
                'children',
            )->has(Book::factory(3))
            ->createOne();
        request()->merge([
            'include' => 'children.books,parent',
        ]);
        $this->repository = new class extends UserRepository {
            public function shouldEagerLoadIncludes(): bool
            {
                return true;
            }
        };

        expect($this->user->relationLoaded('books'))->toBeFalse()
            ->and($this->user->relationLoaded('parent'))->toBeFalse()
            ->and($this->user->children->first()->relationLoaded('books'))->toBeFalse();
    });

    it('can eager load requested includes', function (): void {
        $result = $this->repository
            ->with('comments')
            ->findById($this->user->id);

        expect($result->relationLoaded('children'))->toBeTrue()
            ->and($result->relationLoaded('parent'))->toBeTrue()
            ->and($result->relationLoaded('comments'))->toBeTrue()
            ->and($result->children->first()->relationLoaded('books'))->toBeTrue();
    });

    it('cant automatically include default includes', function (): void {
        $result = $this->repository
            ->findById($this->user->id);
        expect($result->relationLoaded('comments'))->toBeFalse();

        $result = $this->repository
            ->with('comments')
            ->findById($this->user->id);

        expect($result->relationLoaded('comments'))->toBeTrue();
    });

    it('excluding includes will not eager load them', function (): void {
        request()->merge([
            'exclude' => 'children.books,parent',
        ]);

        $result = $this->repository
            ->with('comments')
            ->findById($this->user->id);

        expect($result->relationLoaded('children'))->toBeTrue()
            ->and($result->relationLoaded('parent'))->toBeFalse()
            ->and($result->relationLoaded('comments'))->toBeTrue()
            ->and($result->children->first()->relationLoaded('books'))->toBeFalse();
    })->todo();
})->covers(Response::class, Transformer::class, Repository::class);
