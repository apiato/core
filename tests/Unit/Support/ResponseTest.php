<?php

namespace Tests\Unit\Support;

use Apiato\Support\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use League\Fractal\ParamBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\UnitTestCase;
use Workbench\App\Containers\Identity\User\Data\Repositories\UserRepository;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\Identity\User\UI\API\Transformers\UserTransformer;
use Workbench\App\Containers\MySection\Book\Data\Factories\BookFactory;

#[CoversClass(Response::class)]
class ResponseTest extends UnitTestCase
{
    private const FIELDSET_KEY = 'fields';
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

    public static function fieldsetDataProvider(): array
    {
        return [
            'without includes' => [
                self::FIELDSET_KEY => ['User' => 'id,email'],
                'expected' => ['data.id', 'data.email'],
                'missing' => ['data.object', 'data.name', 'data.created_at', 'data.updated_at', 'data.children', 'data.books'],
            ],
            'only filter nested include keys' => [
                self::FIELDSET_KEY => ['Book' => 'author,title'],
                'expected' => ['data.object', 'data.id', 'data.email', 'data.name', 'data.created_at', 'data.updated_at', 'data.books.data.0.author', 'data.books.data.0.title'],
                'missing' => ['data.books.data.0.id', 'data.books.data.0.created_at', 'data.books.data.0.updated_at'],
            ],
            'with first level includes - no filter' => [
                self::FIELDSET_KEY => ['User' => 'object,id,email,books'],
                'expected' => ['data.object', 'data.id', 'data.email', 'data.books.data.0.object', 'data.books.data.0.id', 'data.books.data.0.title', 'data.books.data.0.author', 'data.books.data.0.created_at', 'data.books.data.0.updated_at'],
                'missing' => ['data.name', 'data.created_at', 'data.updated_at'],
            ],
            'with first level includes - filter' => [
                self::FIELDSET_KEY => ['User' => 'object,id,email,books', 'Book' => 'object,author'],
                'expected' => ['data.object', 'data.id', 'data.email', 'data.books.data.0.object', 'data.books.data.0.author'],
                'missing' => ['data.children', 'data.books.data.0.id', 'data.books.data.0.title', 'data.books.data.0.created_at', 'data.books.data.0.updated_at', 'data.name', 'data.created_at', 'data.updated_at'],
            ],
            'with nested includes - no filter' => [
                self::FIELDSET_KEY => ['User' => 'object,id,email,children,books'],
                'expected' => ['data.object', 'data.id', 'data.email', 'data.children.data.0.object', 'data.children.data.0.id', 'data.children.data.0.email', 'data.children.data.0.books.data.0.object', 'data.children.data.0.books.data.0.id', 'data.children.data.0.books.data.0.title', 'data.children.data.0.books.data.0.author', 'data.children.data.0.books.data.0.created_at', 'data.children.data.0.books.data.0.updated_at'],
                'missing' => ['data.name', 'data.created_at', 'data.updated_at'],
            ],
            'with nested includes - filter' => [
                self::FIELDSET_KEY => ['User' => 'id,email,children,books', 'Book' => 'id'],
                'expected' => ['data.id', 'data.email', 'data.children.data.0.id', 'data.children.data.0.email', 'data.children.data.0.books.data.0.id'],
                'missing' => ['data.object', 'data.children.data.0.object', 'data.children.data.0.books.data.0.object', 'data.children.data.0.books.data.0.title', 'data.children.data.0.books.data.0.author', 'data.children.data.0.books.data.0.created_at', 'data.children.data.0.books.data.0.updated_at', 'data.name', 'data.created_at', 'data.updated_at'],
            ],
        ];
    }

    #[DataProvider('csvIncludeDataProvider')]
    public function testSingleResourceCanHandleCSVInclude(string $include, array $expected): void
    {
        request()->merge(['include' => $include]);
        $response = Response::create($this->user);
        $response->transformWith(UserTransformer::class);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->has($expectation);
        }
    }

    #[DataProvider('arrayIncludeDataProvider')]
    public function testSingleResourceCanHandleArrayInclude(array $include, array $expected): void
    {
        request()->merge(['include' => $include]);
        $response = Response::create($this->user);
        $response->transformWith(UserTransformer::class);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->has($expectation);
        }
    }

    #[DataProvider('paginatedIncludeMetaDataDataProvider')]
    public function testPaginatedResourceMetaDataAndInclude(string|array $include): void
    {
        request()->merge(['include' => $include]);
        User::factory()->count(3)->create();
        $users = app(UserRepository::class, ['app' => $this->app])->paginate();
        $response = Response::create($users)->transformWith(UserTransformer::class);

        $result = AssertableJson::fromArray($response->toArray());

        $result->has('meta.include', fn (AssertableJson $json): AssertableJson => $json->whereAll(['parent', 'children', 'books']));
    }

    #[DataProvider('fieldsetDataProvider')]
    public function testCanFilterResponse(array $fields, array $expected, array $missing): void
    {
        request()->merge(['include' => 'books,children.books', self::FIELDSET_KEY => $fields]);
        $response = Response::create($this->user);
        $response->transformWith(UserTransformer::class);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->has($expectation);
            $result->has('meta.include', fn (AssertableJson $json): AssertableJson => $json->whereAll(['parent', 'children', 'books']));
        }
        foreach ($missing as $expectation) {
            $result->missing($expectation);
        }
    }

    #[DataProvider('csvExcludeDataProvider')]
    public function testSingleResourceCanHandleCSVExclude(string $exclude, array $expected): void
    {
        request()->merge(['exclude' => $exclude]);
        $response = Response::create($this->user);
        $response->transformWith(UserTransformer::class)->parseIncludes($exclude);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->missing($expectation);
        }
    }

    #[DataProvider('arrayExcludeDataProvider')]
    public function testSingleResourceCanHandleArrayExclude(array $exclude, array $expected): void
    {
        request()->merge(['exclude' => $exclude]);
        $response = Response::create($this->user);
        $response->transformWith(UserTransformer::class)->parseIncludes($exclude);

        $result = AssertableJson::fromArray($response->toArray());

        foreach ($expected as $expectation) {
            $result->missing($expectation);
        }
    }

    #[DataProvider('paginatedExcludeMetaDataDataProvider')]
    public function testPaginatedResourceMetaDataAndExclude(string|array $exclude): void
    {
        request()->merge(['exclude' => $exclude]);
        User::factory()->count(3)->create();
        $users = app(UserRepository::class, ['app' => $this->app])->paginate();
        $response = Response::create($users)->transformWith(UserTransformer::class)->parseIncludes($exclude);

        $result = AssertableJson::fromArray($response->toArray());

        $result->has('meta.include', fn (AssertableJson $json): AssertableJson => $json->whereAll(['parent', 'children', 'books']));
    }

    #[DataProvider('validResourceNameProvider')]
    public function testCanOverrideMainResourceName(string $resourceName): void
    {
        request()->merge(['include' => 'books,children.books', self::FIELDSET_KEY => [$resourceName => 'id', 'Book' => 'author,title']]);
        $response = Response::create($this->user);
        $response->transformWith(UserTransformer::class);
        $response->withResourceName($resourceName);

        $result = AssertableJson::fromArray($response->toArray());

        $result->missing('data.object');
    }

    #[DataProvider('invalidResourceNameProvider')]
    public function testGivenInvalidNameProvidedRevertToDefaultMainResourceName(bool|null $resourceName): void
    {
        request()->merge(['include' => 'books,children.books', self::FIELDSET_KEY => [$resourceName => 'id', 'Book' => 'author,title']]);
        $response = Response::create($this->user);
        $response->transformWith(UserTransformer::class);
        $response->withResourceName($resourceName);

        $result = AssertableJson::fromArray($response->toArray());

        $result->has('data.object');
    }

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('fractal.auto_fieldsets.enabled', true);
        config()->set('fractal.auto_fieldsets.request_key', self::FIELDSET_KEY);

        $this->user = User::factory()
            ->for(User::factory()->has(BookFactory::new()), 'parent')
            ->has(User::factory()->has(BookFactory::new())->count(2), 'children')
            ->has(BookFactory::new()->count(2))
            ->createOne();
    }

    public function testCanGenerate200OKResponse(): void
    {
        $response = Response::create($this->user);
        $response->transformWith(UserTransformer::class);

        $result = $response->ok();

        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testCanGenerate202OAcceptedResponse(): void
    {
        $response = Response::create($this->user);
        $response->transformWith(UserTransformer::class);

        $result = $response->accepted();

        $this->assertEquals(202, $result->getStatusCode());
    }

    public function testCanGenerate201CreatedResponse(): void
    {
        $response = Response::create($this->user);
        $response->transformWith(UserTransformer::class);

        $result = $response->created();

        $this->assertEquals(201, $result->getStatusCode());
    }

    public function testCanGenerate204NoContentResponse(): void
    {
        $response = Response::create($this->user);
        $response->transformWith(UserTransformer::class);

        $result = $response->noContent();

        $this->assertEquals(204, $result->getStatusCode());
    }

    public function testCanGetRequestedIncludes(): void
    {
        request()->merge(['include' => 'books,children.books']);

        $result = Response::getRequestedIncludes();

        $this->assertEquals(['books', 'children', 'children.books'], $result);
    }

    public function testCanProcessIncludeParamsWithResourceName(): void
    {
        $include = 'books';
        $includeWithParams = "$include:test(2|value)";
        request()->merge(['include' => $includeWithParams]);
        $response = Response::create($this->user);
        $response->transformWith(UserTransformer::class);
        $response->withResourceName('User');

        $response->respond();

        $scope = $response->getTransformer()?->getCurrentScope();
        $identifier = $scope?->getIdentifier($include);
        $actualParams = $scope?->getManager()->getIncludeParams($identifier);
        $expectedParams = new ParamBag([
            'test' => ['2', 'value'],
        ]);
        $this->assertEquals($expectedParams, $actualParams);
    }
}
