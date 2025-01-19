<?php

use Apiato\Abstract\Transformers\Transformer;
use Workbench\App\Containers\MySection\Book\Models\Book;
use Workbench\App\Containers\MySection\Book\UI\API\Transformers\BookTransformer;

describe(class_basename(Transformer::class), function (): void {
    it('can force valid includes', function (): void {
        config(['apiato.requests.force-valid-includes' => true]);
        $transformer = new BookTransformer();
        $transformer->setDefaultIncludes(['invalid']);

        expect(fn () => Apiato\Support\Response::create(Book::factory()->makeOne(), $transformer)
            ->parseIncludes(['invalid'])->toArray())
            ->toThrow(TypeError::class);
    });
});
