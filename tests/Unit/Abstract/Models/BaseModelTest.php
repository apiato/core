<?php

use Apiato\Abstract\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        $usingFactory = Book::factory()->makeOne();

        expect($usingModel)->toBeInstanceOf(Book::class)
            ->and($usingFactory)->toBeInstanceOf(Book::class);
    });

    it('should have a resource key', function (): void {
        $book = Book::factory()->makeOne();

        expect($book->getResourceKey())->toBe(class_basename($book))
            ->and($this->model->getResourceKey())->toBe('custom-resource-key');
    });

    describe('getHashedKey()', function (): void {
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

    describe('hashed id route model binding', function (): void {
        it('can handle hashed ids', function (): void {
            config(['apiato.hash-id' => true]);
            Book::factory()->count(3)->create();
            $target = Book::factory()->createOne();

            expect(
                Book::newModelInstance()->resolveRouteBinding(
                    hashids()->encode($target->getKey()),
                )->is($target),
            )->toBeTrue();
        });

        it(
            'can detect when it should resolve hashed ids and when it should not',
            function (bool $enabled, bool $incrementing, bool $isHashedId, bool $expectation): void {
                config(['apiato.hash-id' => $enabled]);

                expect(
                    Book::newModelInstance()
                    ->setIncrementing($incrementing)
                    ->shouldProcessHashIdRouteBinding(!$isHashedId ?: hashids()->encode(1)),
                )->toBe($expectation, "Enabled: {$enabled}, Incrementing: {$incrementing}");
            },
        )->with([
            [true, true, true, true],
            [true, false, true, true],
            [false, true, true, false],
            [false, false, true, false],

            [true, true, true, true],
            [true, false, true, true],
            [false, true, true, false],
            [false, false, true, false],

            [true, true, true, true],
            [true, false, true, true],
            [false, true, true, false],
            [false, false, true, false],

            [true, true, false, false],
            [true, false, false, false],
            [false, true, false, false],
            [false, false, false, false],
        ]);

        it(
            'can detect when it should resolve hashed ids and when it should not 2',
            function (string $value, string|null $field, Book $target): void {
                config(['apiato.hash-id' => true]);

                expect(
                    Book::newModelInstance()->resolveRouteBinding(
                        $value,
                        $field,
                    )->is($target),
                )->toBeTrue();
            },
        )->with([
            function (): array {
                $target = Book::factory()->createOne();

                return [hashids()->encode($target->id), null, $target];
            },
            function (): array {
                $target = Book::factory()->createOne();

                return [hashids()->encode($target->id), 'id', $target];
            },
            function (): array {
                $target = Book::factory()->createOne();

                return [$target->title, 'title', $target];
            },
        ])->skip();

        it('can handle unhashed ids', function (): void {
            config(['apiato.hash-id' => false]);
            Book::factory()->count(3)->create();
            $target = Book::factory()->createOne([
                'title' => 'Target',
            ]);

            expect(
                Book::newModelInstance()->resolveRouteBinding(
                    $target->getKey(),
                )->is($target),
            )->toBeTrue()
                ->and(
                    Book::newModelInstance()->resolveRouteBinding(
                        $target->title,
                        'title',
                    )->is($target),
                )->toBeTrue();
        });
    });
})->covers(BaseModel::class);
