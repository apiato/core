<?php

namespace Apiato\Core\Tests\Unit\Services;

use Apiato\Core\Services\Response;
use Apiato\Core\Tests\Infrastructure\Doubles\BookFactory;
use Apiato\Core\Tests\Infrastructure\Doubles\User;
use Apiato\Core\Tests\Infrastructure\Doubles\UserFactory;
use Apiato\Core\Tests\Infrastructure\Doubles\UserRepository;
use Apiato\Core\Tests\Infrastructure\Doubles\UserTransformer;
use Apiato\Core\Tests\Unit\UnitTestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;

class ResponseTest extends UnitTestCase
{
    private const FIELDSET_KEY = 'fieldset';
    private User $user;

    public static function csvIncludeDataProvider(): array
    {
        return [
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
        ];
    }

    public static function arrayIncludeDataProvider(): array
    {
        return [
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
        ];
    }

    public static function paginatedIncludeMetaDataDataProvider(): array
    {
        return [
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
        ];
    }

    public static function fieldsetDataProvider(): array
    {
        return [
            'without includes' => [
                self::FIELDSET_KEY => ['User:id;email'],
                'expected' => ['data.id', 'data.email'],
                'missing' => ['data.object', 'data.name', 'data.created_at', 'data.updated_at', 'data.children', 'data.books'],
            ],
            'only filter nested include keys' => [
                self::FIELDSET_KEY => ['Book:author;title'],
                'expected' => ['data.object', 'data.id', 'data.email', 'data.name', 'data.created_at', 'data.updated_at', 'data.books.data.0.author', 'data.books.data.0.title'],
                'missing' => ['data.books.data.0.id', 'data.books.data.0.created_at', 'data.books.data.0.updated_at'],
            ],
            'with first level includes - no filter' => [
                self::FIELDSET_KEY => ['User:object,id;email;books'],
                'expected' => ['data.object', 'data.id', 'data.email', 'data.books.data.0.object', 'data.books.data.0.id', 'data.books.data.0.title', 'data.books.data.0.author', 'data.books.data.0.created_at', 'data.books.data.0.updated_at'],
                'missing' => ['data.name', 'data.created_at', 'data.updated_at'],
            ],
            'with first level includes - filter' => [
                self::FIELDSET_KEY => ['User:object,id;email;books', 'Book:object,author'],
                'expected' => ['data.object', 'data.id', 'data.email', 'data.books.data.0.object', 'data.books.data.0.author'],
                'missing' => ['data.children', 'data.books.data.0.id', 'data.books.data.0.title', 'data.books.data.0.created_at', 'data.books.data.0.updated_at', 'data.name', 'data.created_at', 'data.updated_at'],
            ],
            'with nested includes - no filter' => [
                self::FIELDSET_KEY => ['User:object,id;email,children;books'],
                'expected' => ['data.object', 'data.id', 'data.email', 'data.children.data.0.object', 'data.children.data.0.id', 'data.children.data.0.email', 'data.children.data.0.books.data.0.object', 'data.children.data.0.books.data.0.id', 'data.children.data.0.books.data.0.title', 'data.children.data.0.books.data.0.author', 'data.children.data.0.books.data.0.created_at', 'data.children.data.0.books.data.0.updated_at'],
                'missing' => ['data.name', 'data.created_at', 'data.updated_at'],
            ],
            'with nested includes - filter' => [
                self::FIELDSET_KEY => ['User:id;email;children;books', 'Book:id'],
                'expected' => ['data.id', 'data.email', 'data.children.data.0.id', 'data.children.data.0.email', 'data.children.data.0.books.data.0.id'],
                'missing' => ['data.object', 'data.children.data.0.object', 'data.children.data.0.books.data.0.object', 'data.children.data.0.books.data.0.title', 'data.children.data.0.books.data.0.author', 'data.children.data.0.books.data.0.created_at', 'data.children.data.0.books.data.0.updated_at', 'data.name', 'data.created_at', 'data.updated_at'],
            ],
        ];
    }

    public static function csvExcludeDataProvider(): array
    {
        return [
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
        ];
    }

    public static function arrayExcludeDataProvider(): array
    {
        return [
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
        ];
    }

    public static function paginatedExcludeMetaDataDataProvider(): array
    {
        return [
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
        ];
    }

    public static function validResourceNameProvider(): array
    {
        return [
            'empty string' => [
                'resourceName' => '',
            ],
            'string' => [
                'resourceName' => 'wat',
            ],
        ];
    }

    public static function invalidResourceNameProvider(): array
    {
        return [
            'null' => [
                'resourceName' => null,
            ],
            'false' => [
                'resourceName' => false,
            ],
        ];
    }

    #[DataProvider('csvIncludeDataProvider')]
    public function testSingleResourceCanHandleCSVInclude($include, $expected): void
    {
        request()->merge(compact('include'));
        $response = Response::createFrom($this->user);
        $response->transformWith(UserTransformer::class);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->has($expectation);
        }
    }

    #[DataProvider('arrayIncludeDataProvider')]
    public function testSingleResourceCanHandleArrayInclude($include, $expected): void
    {
        request()->merge(compact('include'));
        $response = Response::createFrom($this->user);
        $response->transformWith(UserTransformer::class);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->has($expectation);
        }
    }

    #[DataProvider('paginatedIncludeMetaDataDataProvider')]
    public function testPaginatedResourceMetaDataAndInclude($include): void
    {
        request()->merge(compact('include'));
        UserFactory::new()->count(3)->create();
        $users = app(UserRepository::class, ['app' => $this->app])->paginate();
        $response = Response::createFrom($users)->transformWith(UserTransformer::class);

        $result = AssertableJson::fromArray($response->toArray());

        $result->has('meta.include', fn (AssertableJson $json) => $json->whereAll(['parent', 'children', 'books']));
    }

    #[DataProvider('fieldsetDataProvider')]
    public function testCanFilterResponse($fieldset, $expected, $missing): void
    {
        request()->merge(['include' => 'books,children.books', self::FIELDSET_KEY => $fieldset]);
        $response = Response::createFrom($this->user);
        $response->transformWith(UserTransformer::class);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->has($expectation);
            $result->has('meta.include', fn (AssertableJson $json) => $json->whereAll(['parent', 'children', 'books']));
        }
        foreach ($missing as $expectation) {
            $result->missing($expectation);
        }
    }

    #[DataProvider('csvExcludeDataProvider')]
    public function testSingleResourceCanHandleCSVExclude($exclude, $expected): void
    {
        request()->merge(compact('exclude'));
        $response = Response::createFrom($this->user);
        $response->transformWith(UserTransformer::class)->parseIncludes($exclude);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->missing($expectation);
        }
    }

    #[DataProvider('arrayExcludeDataProvider')]
    public function testSingleResourceCanHandleArrayExclude($exclude, $expected): void
    {
        request()->merge(compact('exclude'));
        $response = Response::createFrom($this->user);
        $response->transformWith(UserTransformer::class)->parseIncludes($exclude);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->missing($expectation);
        }
    }

    #[DataProvider('paginatedExcludeMetaDataDataProvider')]
    public function testPaginatedResourceMetaDataAndExclude($exclude): void
    {
        request()->merge(compact('exclude'));
        UserFactory::new()->count(3)->create();
        $users = app(UserRepository::class, ['app' => $this->app])->paginate();
        $response = Response::createFrom($users)->transformWith(UserTransformer::class)->parseIncludes($exclude);

        $result = AssertableJson::fromArray($response->toArray());

        $result->has('meta.include', fn (AssertableJson $json) => $json->whereAll(['parent', 'children', 'books']));
    }

    #[DataProvider('validResourceNameProvider')]
    public function testCanOverrideMainResourceName($resourceName): void
    {
        request()->merge(['include' => 'books,children.books', self::FIELDSET_KEY => ["{$resourceName}:id", 'Book:author,title']]);
        $response = Response::createFrom($this->user);
        $response->transformWith(UserTransformer::class);
        $response->withResourceName($resourceName);

        $result = AssertableJson::fromArray($response->toArray());

        $result->missing('data.object');
    }

    #[DataProvider('invalidResourceNameProvider')]
    public function testGivenInvalidNameProvidedRevertToDefaultMainResourceName($resourceName): void
    {
        request()->merge(['include' => 'books,children.books', self::FIELDSET_KEY => ["{$resourceName}:id", 'Book:author,title']]);
        $response = Response::createFrom($this->user);
        $response->transformWith(UserTransformer::class);
        $response->withResourceName($resourceName);

        $result = AssertableJson::fromArray($response->toArray());

        $result->has('data.object');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('apiato.requests.params.filter', self::FIELDSET_KEY);

        $this->user = UserFactory::new()
            ->for(UserFactory::new()->has(BookFactory::new()), 'parent')
            ->has(UserFactory::new()->has(BookFactory::new())->count(2), 'children')
            ->has(BookFactory::new()->count(2))
            ->createOne();
    }
}
