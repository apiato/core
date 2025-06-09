<?php

declare(strict_types=1);

use Apiato\Core\Models\BaseModel;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\MySection\Book\Models\Book;

describe(class_basename(BaseModel::class), function (): void {
    it(
        'can handle hashed id route model binding',
        function (): void {
            config(['apiato.hash-id' => true]);
            $model = User::factory()
                ->has(User::factory(1, ['name' => 'child_name'])
                    ->has(Book::factory(2)), 'children')
                ->createOne();

            $response = $this->getJson(
                sprintf('v1/authors/%s/children/%s/books/%s', $model->getHashedKey(), $model->children->first()->name, $model->children->first()->books->last()->getHashedKey()),
            );

            expect($response->content())->toBe($model->children->first()->books->last()->author->name);
        },
    );
});
