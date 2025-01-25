<?php

use Apiato\Abstract\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Workbench\App\Containers\MySection\Book\Data\Factories\BookFactory;
use Workbench\App\Containers\MySection\Book\Models\Book;

describe(class_basename(BaseModel::class), function (): void {
    beforeEach(function (): void {
        $this->model = new class extends BaseModel {
            protected $resourceKey = 'custom-resource-key';
        };
    });

    it('should extend the Laravel Eloquent Model', function (): void {
        expect(BaseModel::class)->toExtend(Model::class);
    });

    it('should use expected traits', function (): void {
        $traits = class_uses_recursive($this->model);

        expect($traits)->toContain(
            HasFactory::class,
        );
    });

    it('can locate the factory of the model using different call styles', function (): void {
        $usingModel = Book::factory()->makeOne();
        $usingFactory = BookFactory::new()->makeOne();

        expect($usingModel)->toBeInstanceOf(Book::class)
            ->and($usingFactory)->toBeInstanceOf(Book::class);
    });

    it('should have a resource key', function (): void {
        $book = Book::factory()->makeOne();

        expect($book->getResourceKey())->toBe(class_basename($book))
            ->and($this->model->getResourceKey())->toBe('custom-resource-key');
    });

    describe('::getHashedKey', function (): void {
        beforeEach(function (): void {
            config(['apiato.hash-id' => true]);
        });

        it('returns hashed primary key by default', function (): void {
            $book = Book::factory()->createOne();

            expect($book->getHashedKey())->toBe(hashids()->encode($book->getKey()));
        });

        it('can return hashed key for a specific field', function (): void {
            $book = Book::factory()->makeOne();

            expect($book->getHashedKey('author_id'))->toBe(hashids()->encode($book->author_id));
        });

        it('returns null if the field is null', function (): void {
            $book = Book::factory()->makeOne(['author_id' => null]);

            expect($book->getHashedKey('author_id'))->toBeNull();
        });

        it('returns the original field value if hash-id is disabled', function (): void {
            config(['apiato.hash-id' => false]);
            $book = Book::factory()->makeOne();

            expect($book->getHashedKey())->toEqual($book->getKey());
        });

        it('returns the original field value if the field is not hashable and hash-id is disabled', function (): void {
            config(['apiato.hash-id' => false]);
            $book = Book::factory()->makeOne();

            expect($book->getHashedKey('title'))->toEqual($book->title);
        });

        it('throws an exception if the value cannot be encoded', function (): void {
            $book = Book::factory()->makeOne(['author_id' => 'invalid-id']);

            expect(fn () => $book->getHashedKey('author_id'))
                ->toThrow(new RuntimeException('Failed to encode the given value.'));
        });
    });
})->covers(BaseModel::class);
