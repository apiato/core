<?php

declare(strict_types=1);

use Apiato\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Workbench\App\Containers\MySection\Book\Models\Book;

describe(class_basename(BaseModel::class), function (): void {
    beforeEach(function (): void {
        $this->model = new class () extends BaseModel {
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
        $model = Book::factory()->makeOne();

        expect($usingModel)->toBeInstanceOf(Book::class)
            ->and($model)->toBeInstanceOf(Book::class);
    });

    describe('getHashedKey()', function (): void {
        beforeEach(function (): void {
            config(['apiato.hash-id' => true]);
        });

        it('returns hashed primary key by default', function (): void {
            $model = Book::factory()->createOne();

            expect($model->getHashedKey())->toBe(hashids()->encode($model->getKey()));
        });

        it('can return hashed key for a specific field', function (): void {
            $model = Book::factory()->makeOne();

            expect($model->getHashedKey('author_id'))->toBe(hashids()->encode($model->author_id));
        });

        it('returns null if the field is null', function (): void {
            $model = Book::factory()->makeOne(['author_id' => null]);

            expect($model->getHashedKey('author_id'))->toBeNull();
        });

        it('returns the original field value if hash-id is disabled', function (): void {
            config(['apiato.hash-id' => false]);
            $model = Book::factory()->makeOne();

            expect($model->getHashedKey())->toEqual($model->getKey());
        });

        it('returns the original field value if the field is not hashable and hash-id is disabled', function (): void {
            config(['apiato.hash-id' => false]);
            $model = Book::factory()->makeOne();

            expect($model->getHashedKey('title'))->toEqual($model->title);
        });
    });

    describe('hashed id route model binding', function (): void {
        it('can handle hashed ids', function (): void {
            config(['apiato.hash-id' => true]);
            Book::factory()->count(3)->create();
            $model = Book::factory()->createOne();

            expect(
                Book::newModelInstance()->resolveRouteBinding(
                    hashids()->encode($model->getKey()),
                )->is($model),
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
                )->toBe($expectation, sprintf('Enabled: %s, Incrementing: %s', $enabled, $incrementing));
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
            function (string $value, null|string $field, Book $target): void {
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
                $model = Book::factory()->createOne();

                return [hashids()->encode($model->id), null, $model];
            },
            function (): array {
                $model = Book::factory()->createOne();

                return [hashids()->encode($model->id), 'id', $model];
            },
            function (): array {
                $model = Book::factory()->createOne();

                return [$model->title, 'title', $model];
            },
        ])->skip();

        it('can handle unhashed ids', function (): void {
            config(['apiato.hash-id' => false]);
            Book::factory()->count(3)->create();
            $model = Book::factory()->createOne([
                'title' => 'Target',
            ]);

            expect(
                Book::newModelInstance()->resolveRouteBinding(
                    $model->getKey(),
                )->is($model),
            )->toBeTrue()
                ->and(
                    Book::newModelInstance()->resolveRouteBinding(
                        $model->title,
                        'title',
                    )->is($model),
                )->toBeTrue();
        });
    });
})->covers(BaseModel::class);
