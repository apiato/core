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
                'expected' => ['parent', 'children', 'books']
            ],
            'single string nested' => [
                'include' => 'children.books',
                'expected' => ['parent', 'children', 'books']
            ],
            'csv string' => [
                'include' => 'parent,children',
                'expected' => ['parent', 'children', 'books']
            ],
            'csv string and nested' => [
                'include' => 'parent,children.books',
                'expected' => ['parent', 'children', 'books']
            ],
            'single array' => [
                'include' => ['parent'],
                'expected' => ['parent', 'children', 'books']
            ],
            'multiple array' => [
                'include' => ['parent', 'children'],
                'expected' => ['parent', 'children', 'books']
            ],
            'multiple array nested' => [
                'include' => ['parent.books', 'children'],
                'expected' => ['parent', 'children', 'books']
            ],
        ];
    }

    #[DataProvider('paginatedIncludeMetaDataDataProvider')]
    public function testPaginatedResourceMetaData($include, $expected): void
    {
        request()->offsetSet('include', $include);
            UserFactory::new()->count(3)->create();
        $users = app(UserRepository::class, ['app' => $this->app])->paginate();
        $response = Response::createFrom($users)->transformWith(UserTransformer::class);

        $result = AssertableJson::fromArray($response->toArray());

        $result->has('meta.include', fn (AssertableJson $json) => $json->whereAll($expected));
    }
}
