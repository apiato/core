<?php

namespace Apiato\Core\Tests\Unit\Services;

use Apiato\Core\Services\Response;
use Apiato\Core\Tests\Infrastructure\Doubles\BookFactory;
use Apiato\Core\Tests\Infrastructure\Doubles\User;
use Apiato\Core\Tests\Infrastructure\Doubles\UserFactory;
use Apiato\Core\Tests\Infrastructure\Doubles\UserRepository;
use Apiato\Core\Tests\Infrastructure\Doubles\UserTransformer;
use Apiato\Core\Tests\Unit\UnitTestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;

class ResponseTest extends UnitTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserFactory::new()
            ->for(UserFactory::new()->has(BookFactory::new()), 'parent')
            ->has(UserFactory::new()->has(BookFactory::new())->count(2), 'children')
            ->has(BookFactory::new()->count(2))
            ->createOne();
    }

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

    public static function fieldsetDataProvider(): array
    {
        return [
            'without includes' => [
                'fieldset' => ['User:id;email'],
                'expected' => ['data.id', 'data.email'],
            ],
            'with first level includes - no filter' => [
                'fieldset' => ['User:object,id;email;books'],
                'expected' => ['data.object', 'data.id', 'data.email', 'data.books.data.0.object', 'data.books.data.0.id', 'data.books.data.0.title', 'data.books.data.0.author', 'data.books.data.0.created_at', 'data.books.data.0.updated_at'],
            ],
            'with first level includes - filter' => [
                'fieldset' => ['User:object,id;email;books', 'Book:object,author'],
                'expected' => ['data.object', 'data.id', 'data.email', 'data.books.data.0.object', 'data.books.data.0.author'],
            ],
            'with nested includes - no filter' => [
                'fieldset' => ['User:object,id;email,children;books'],
                'expected' => ['data.object', 'data.id', 'data.email', 'data.children.data.0.object', 'data.children.data.0.id', 'data.children.data.0.email', 'data.children.data.0.books.data.0.object', 'data.children.data.0.books.data.0.id', 'data.children.data.0.books.data.0.title', 'data.children.data.0.books.data.0.author', 'data.children.data.0.books.data.0.created_at', 'data.children.data.0.books.data.0.updated_at'],
            ],
            'with nested includes - filter' => [
                'fieldset' => ['User:object,id;email;children;books', 'Books:id'],
                'expected' => ['data.object', 'data.id', 'data.email', 'data.children.data.0.object', 'data.children.data.0.id', 'data.children.data.0.email', 'data.children.data.0.books.data.0.id'],
            ],
        ];
    }

    #[DataProvider('fieldsetDataProvider')]
    public function testCanFilterResponse($fieldset, $expected): void
    {
        request()->merge(['include' => 'books,children.books', 'fieldset' => $fieldset]);
        $response = Response::createFrom($this->user);
        $response->transformWith(UserTransformer::class);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->has($expectation);
            $result->has('meta.include', fn (AssertableJson $json) => $json->whereAll(['parent', 'children', 'books']));
        }
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
}
