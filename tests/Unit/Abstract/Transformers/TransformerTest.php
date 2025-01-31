<?php

use Apiato\Abstract\Transformers\Transformer;
use Apiato\Support\Resources\Collection;
use Apiato\Support\Resources\Item;
use League\Fractal\Resource\Primitive;
use Workbench\App\Containers\MySection\Book\Models\Book;
use Workbench\App\Containers\MySection\Book\UI\API\Transformers\BookTransformer;

describe(class_basename(Transformer::class), function (): void {
    it('can return a nullable item', function (): void {
        $transformer = new BookTransformer();
        $transformer->setDefaultIncludes(['author']);

        $item = $transformer->nullableItem(null, new BookTransformer());
        expect($item)->toBeInstanceOf(Primitive::class);
        expect($item->getData())->toBeNull();

        $item = $transformer->nullableItem(Book::factory()->makeOne(), new BookTransformer());
        expect($item)->toBeInstanceOf(Item::class);
    });

    dataset('resourceKeys', [
        'null resource key' => [null, 'Book'],
        'override resource key' => ['CustomKey', 'CustomKey'],
    ]);

    it('can return an item', function (string|null $resourceKey, $expected): void {
        $transformer = new BookTransformer();
        $transformer->setDefaultIncludes(['author']);

        $item = $transformer->item(
            Book::factory()->makeOne(),
            new BookTransformer(),
            $resourceKey,
        );

        expect($item)->toBeInstanceOf(Item::class)
            ->and($item->getResourceKey())->toBe($expected);
    })->with('resourceKeys');

    it('can return a collection', function (string|null $resourceKey, $expected): void {
        $transformer = new BookTransformer();
        $transformer->setDefaultIncludes(['author']);

        $collection = $transformer->collection(
            Book::factory()->count(3)->make(),
            new BookTransformer(),
            $resourceKey,
        );

        expect($collection)->toBeInstanceOf(Collection::class)
            ->and($collection->getResourceKey())->toBe($expected);
    })->with('resourceKeys');

    it('can force valid includes', function (): void {
        config(['apiato.requests.force-valid-includes' => true]);
        $transformer = new BookTransformer();
        $transformer->setDefaultIncludes(['invalid']);

        expect(fn () => Apiato\Support\Response::create(Book::factory()->makeOne(), $transformer)
            ->parseIncludes(['invalid'])->toArray())
            ->toThrow(TypeError::class);
    });

    it('can return empty transformer', function (): void {
        $transformer = new BookTransformer();

        $emptyTransformer = $transformer::empty();
        expect($emptyTransformer)->toBeInstanceOf(Closure::class);
    });
});
