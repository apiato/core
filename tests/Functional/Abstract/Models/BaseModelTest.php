<?php

use Apiato\Core\Models\BaseModel;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\MySection\Book\Models\Book;

describe(class_basename(BaseModel::class), function (): void {
    it(
        'can handle hashed id route model binding',
        function (): void {
            config(['apiato.hash-id' => true]);
            $user = User::factory()
                ->has(User::factory(1, ['name' => 'child_name'])
                    ->has(Book::factory(2)), 'children')
                ->createOne();

            $response = $this->getJson(
                "v1/authors/{$user->getHashedKey()}/children/{$user->children->first()->name}/books/{$user->children->first()->books->last()->getHashedKey()}",
            );

            expect($response->content())->toBe($user->children->first()->books->last()->author->name);
        },
    );
});
