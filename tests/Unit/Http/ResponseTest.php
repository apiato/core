<?php

namespace Tests\Unit\Support;

use Apiato\Http\Resources\Item;
use Apiato\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Testing\Fluent\AssertableJson;
use League\Fractal\Manager;
use League\Fractal\ParamBag;
use League\Fractal\Resource\NullResource;
use Workbench\App\Containers\Identity\User\Data\Repositories\UserRepository;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\Identity\User\UI\API\Transformers\UserTransformer;
use Workbench\App\Containers\MySection\Book\Models\Book;

describe(class_basename(Response::class), function (): void {
    beforeEach(function (): void {
        config(['fractal.auto_fieldsets.enabled' => true]);
        config(['fractal.auto_fieldsets.request_key' => 'fields']);
    });

    function getUser(): User
    {
        return User::factory()
            ->for(User::factory()->has(Book::factory()), 'parent')
            ->has(User::factory(2)->has(Book::factory()), 'children')
            ->has(Book::factory(2))
            ->createOne();
    }

    it('can return its manager instance', function (): void {
        $response = Response::create(getUser());

        $result = $response->manager();

        expect($result)->toBeInstanceOf(Manager::class);
    });

    it('can handle CSV includes for single resource', function (string $include, array $expected): void {
        request()->merge(['include' => $include]);
        $response = Response::create(getUser());
        $response->transformWith(UserTransformer::class);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->has($expectation);
        }
    })->with([
        'single string' => [
            'include' => 'parent',
            'expected' => ['data.parent'],
        ],
        'single string nested' => [
            'include' => 'children.books',
            'expected' => ['data.children.data.0.books'],
        ],
        'csv string' => [
            'include' => 'parent,children',
            'expected' => ['data.parent', 'data.children'],
        ],
        'csv string and nested' => [
            'include' => 'parent,children.books',
            'expected' => ['data.parent', 'data.children.data.0.books'],
        ],
    ]);

    it('can handle array includes for single resource', function (array $include, array $expected): void {
        request()->merge(['include' => $include]);
        $response = Response::create(getUser());
        $response->transformWith(UserTransformer::class);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->has($expectation);
        }
    })->with([
        'single array' => [
            'include' => ['parent'],
            'expected' => ['data.parent'],
        ],
        'multiple array' => [
            'include' => ['parent', 'children'],
            'expected' => ['data.parent', 'data.children'],
        ],
        'multiple array nested' => [
            'include' => ['parent.books', 'children'],
            'expected' => ['data.parent.data.books', 'data.children'],
        ],
    ]);

    it('can handle CSV includes for paginated resource', function (string|array $include): void {
        request()->merge(['include' => $include]);
        User::factory(3)->create();
        $users = app(UserRepository::class, ['app' => $this->app])->paginate();
        $response = Response::create($users)->transformWith(UserTransformer::class);

        $result = AssertableJson::fromArray($response->toArray());

        $result->has('meta.include', fn (AssertableJson $json): AssertableJson => $json->whereAll(['parent', 'children', 'books']));
    })->with([
        'single string' => [
            'include' => 'parent',
        ],
        'single string nested' => [
            'include' => 'children.books',
        ],
        'csv string' => [
            'include' => 'parent,children',
        ],
        'csv string and nested' => [
            'include' => 'parent,children.books',
        ],
        'single array' => [
            'include' => ['parent'],
        ],
        'multiple array' => [
            'include' => ['parent', 'children'],
        ],
        'multiple array nested' => [
            'include' => ['parent.books', 'children'],
        ],
    ]);

    it('can filter response', function (array $fields, array $expected, array $missing): void {
        request()->merge(['include' => 'books,children.books', 'fields' => $fields]);
        $response = Response::create(getUser());
        $response->transformWith(UserTransformer::class);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->has($expectation);
            $result->has('meta.include', fn (AssertableJson $json): AssertableJson => $json->whereAll(['parent', 'children', 'books']));
        }
        foreach ($missing as $expectation) {
            $result->missing($expectation);
        }
    })->with([
        'without includes' => [
            'fields' => ['User' => 'id,email'],
            'expected' => ['data.id', 'data.email'],
            'missing' => ['data.type', 'data.name', 'data.created_at', 'data.updated_at', 'data.children', 'data.books'],
        ],
        'only filter nested include keys' => [
            'fields' => ['Book' => 'author,title'],
            'expected' => ['data.type', 'data.id', 'data.email', 'data.name', 'data.created_at', 'data.updated_at', 'data.books.data.0.author', 'data.books.data.0.title'],
            'missing' => ['data.books.data.0.id', 'data.books.data.0.created_at', 'data.books.data.0.updated_at'],
        ],
        'with first level includes - no filter' => [
            'fields' => ['User' => 'type,id,email,books'],
            'expected' => ['data.type', 'data.id', 'data.email', 'data.books.data.0.type', 'data.books.data.0.id', 'data.books.data.0.title', 'data.books.data.0.author', 'data.books.data.0.created_at', 'data.books.data.0.updated_at'],
            'missing' => ['data.name', 'data.created_at', 'data.updated_at'],
        ],
        'with first level includes - filter' => [
            'fields' => ['User' => 'type,id,email,books', 'Book' => 'type,author'],
            'expected' => ['data.type', 'data.id', 'data.email', 'data.books.data.0.type', 'data.books.data.0.author'],
            'missing' => ['data.children', 'data.books.data.0.id', 'data.books.data.0.title', 'data.books.data.0.created_at', 'data.books.data.0.updated_at', 'data.name', 'data.created_at', 'data.updated_at'],
        ],
        'with nested includes - no filter' => [
            'fields' => ['User' => 'type,id,email,children,books'],
            'expected' => ['data.type', 'data.id', 'data.email', 'data.children.data.0.type', 'data.children.data.0.id', 'data.children.data.0.email', 'data.children.data.0.books.data.0.type', 'data.children.data.0.books.data.0.id', 'data.children.data.0.books.data.0.title', 'data.children.data.0.books.data.0.author', 'data.children.data.0.books.data.0.created_at', 'data.children.data.0.books.data.0.updated_at'],
            'missing' => ['data.name', 'data.created_at', 'data.updated_at'],
        ],
        'with nested includes - filter' => [
            'fields' => ['User' => 'id,email,children,books', 'Book' => 'id'],
            'expected' => ['data.id', 'data.email', 'data.children.data.0.id', 'data.children.data.0.email', 'data.children.data.0.books.data.0.id'],
            'missing' => ['data.type', 'data.children.data.0.type', 'data.children.data.0.books.data.0.type', 'data.children.data.0.books.data.0.title', 'data.children.data.0.books.data.0.author', 'data.children.data.0.books.data.0.created_at', 'data.children.data.0.books.data.0.updated_at', 'data.name', 'data.created_at', 'data.updated_at'],
        ],
    ]);

    it('can handle CSV exclude for single resource', function (string $exclude, array $expected): void {
        request()->merge(['exclude' => $exclude]);
        $response = Response::create(getUser());
        $response->transformWith(UserTransformer::class)->parseIncludes($exclude);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->missing($expectation);
        }
    })->with([
        'single string' => [
            'exclude' => 'parent',
            'expected' => ['data.parent'],
        ],
        'single string nested' => [
            'exclude' => 'children.books',
            'expected' => ['data.children.data.0.books'],
        ],
        'csv string' => [
            'exclude' => 'parent,children',
            'expected' => ['data.parent', 'data.children'],
        ],
        'csv string and nested' => [
            'exclude' => 'parent,children.books',
            'expected' => ['data.parent', 'data.children.data.0.books'],
        ],
    ]);

    it('can handle array exclude for single resource', function (array $exclude, array $expected): void {
        request()->merge(['exclude' => $exclude]);
        $response = Response::create(getUser());
        $response->transformWith(UserTransformer::class)->parseIncludes($exclude);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->missing($expectation);
        }
    })->with([
        'single array' => [
            'exclude' => ['parent'],
            'expected' => ['data.parent'],
        ],
        'multiple array' => [
            'exclude' => ['parent', 'children'],
            'expected' => ['data.parent', 'data.children'],
        ],
        'multiple array nested' => [
            'exclude' => ['parent.books', 'children'],
            'expected' => ['data.parent.data.books', 'data.children'],
        ],
    ]);

    it('can handle CSV exclude for paginated resource', function (string|array $exclude): void {
        request()->merge(['exclude' => $exclude]);
        User::factory(3)->create();
        $users = app(UserRepository::class, ['app' => $this->app])->paginate();
        $response = Response::create($users)->transformWith(UserTransformer::class)->parseIncludes($exclude);

        $result = AssertableJson::fromArray($response->toArray());

        $result->has('meta.include', fn (AssertableJson $json): AssertableJson => $json->whereAll(['parent', 'children', 'books']));
    })->with([
        'single string' => [
            'exclude' => 'parent',
        ],
        'single string nested' => [
            'exclude' => 'children.books',
        ],
        'csv string' => [
            'exclude' => 'parent,children',
        ],
        'csv string and nested' => [
            'exclude' => 'parent,children.books',
        ],
        'single array' => [
            'exclude' => ['parent'],
        ],
        'multiple array' => [
            'exclude' => ['parent', 'children'],
        ],
        'multiple array nested' => [
            'exclude' => ['parent.books', 'children'],
        ],
    ]);

    it('can override resource name', function (string $resourceName): void {
        request()->merge(['include' => 'books,children.books', 'fields' => [$resourceName => 'id', 'Book' => 'author,title']]);
        $response = Response::create(getUser());
        $response->transformWith(UserTransformer::class);
        $response->withResourceName($resourceName);

        $result = AssertableJson::fromArray($response->toArray());

        $result->missing('data.type');
    })->with([
        'empty string' => [
            'resourceName' => '',
        ],
        'string' => [
            'resourceName' => 'wat',
        ],
    ]);

    it('can use fallback default resource name', function (bool|null $resourceName): void {
        request()->merge(['include' => 'books,children.books', 'fields' => [$resourceName => 'id', 'Book' => 'author,title']]);
        $response = Response::create(getUser());
        $response->transformWith(UserTransformer::class);
        $response->withResourceName($resourceName);

        $result = AssertableJson::fromArray($response->toArray());

        $result->has('data.type');
    })->with([
        'null' => [
            'resourceName' => null,
        ],
        'false' => [
            'resourceName' => false,
        ],
    ]);

    it('can generate 200/OK response', function (): void {
        $response = Response::create(getUser());

        $result = $response->ok();

        expect($result)->getStatusCode()->toBe(200)->getData()->toHaveKey('data', []);
    });

    it('can generate 202/Accepted response', function (): void {
        $response = Response::create(getUser());

        $result = $response->accepted();

        expect($result)->getStatusCode()->toBe(202)->getData()->toHaveKey('data', []);
    });

    it('can generate 201/Created response', function (): void {
        $response = Response::create(getUser());

        $result = $response->created();

        expect($result)->getStatusCode()->toBe(201)->getData()->toHaveKey('data', []);
    });

    it('can generate 204/NoContent response', function (): void {
        $response = Response::create(getUser());
        $response->transformWith(UserTransformer::class);

        $result = $response->noContent();

        expect($result->getStatusCode())->toBe(204);
    });

    it('can parse include params with resource name', function (): void {
        $include = 'books';
        $includeWithParams = "$include:test(2|value)";
        request()->merge(['include' => $includeWithParams]);
        $response = Response::create(getUser());
        $response->transformWith(UserTransformer::class);
        $response->withResourceName('User');

        $response->respond();

        $scope = $response->getTransformer()?->getCurrentScope();
        $identifier = $scope?->getIdentifier($include);
        $actualParams = $scope?->getManager()->getIncludeParams($identifier);
        $expectedParams = new ParamBag([
            'test' => ['2', 'value'],
        ]);
        expect($expectedParams)->toEqual($actualParams);
    });

    it('returns the custom resource class', function (Collection|User|array|null $data, string $expectation): void {
        $response = Response::create($data);

        $result = $response->getResourceClass();

        expect($result)->toBe($expectation);
    })->with([
        [fn () => User::factory()->makeOne(), Item::class],
        [Collection::empty(), \Apiato\Http\Resources\Collection::class],
        [[], \Apiato\Http\Resources\Collection::class],
        [null, NullResource::class],
    ]);
})->covers(Response::class);
