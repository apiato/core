<?php

namespace Apiato\Core\Tests\Unit\Abstracts\Repositories;

use Apiato\Core\Abstracts\Repositories\Repository;
use Apiato\Core\Tests\Infrastructure\Doubles\User;
use Apiato\Core\Tests\Infrastructure\Doubles\UserFactory;
use Apiato\Core\Tests\Infrastructure\Doubles\UserRepository;
use Apiato\Core\Tests\Unit\UnitTestCase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('ship')]
#[CoversClass(Repository::class)]
final class RepositoryTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('apiato.requests.params.include', 'include');
    }

    public static function includeDataProvider(): array
    {
        return [
            'single relation' => ['parent', ['parent'], ['children']],
            'multiple relations' => ['parent,children', ['parent', 'children'], []],
            'single nested relation' => ['children.parent', ['children'], []],
            'multiple nested relations' => ['parent.children,children', ['parent', 'children'], []],
            'multiple and single nested relations' => ['parent.children,children.parent', ['parent', 'children'], []],
        ];
    }

    #[DataProvider('includeDataProvider')]
    public function testEagerLoadSingleRelationRequestedViaRequest(string $includes, array $mustLoadRelations, array $mustNotLoadRelations): void
    {
        request()->offsetSet(config('apiato.requests.params.include', 'include'), $includes);
        $parent = UserFactory::new()->has(
            UserFactory::new()->count(3),
            'children',
        )->count(3)->create();
        $repository = app(UserRepository::class);

        // get all children
        $result = $repository->all();

        $result->each(function (User $user) use ($mustLoadRelations, $mustNotLoadRelations) {
            foreach ($mustLoadRelations as $relation) {
                $this->assertTrue($user->relationLoaded($relation));
            }
            foreach ($mustNotLoadRelations as $relation) {
                $this->assertFalse($user->relationLoaded($relation));
            }
        });
    }

    public function testMultipleEagerLoadAppliesAllEagerLoads(): void
    {
        $parent = UserFactory::new()->createOne();
        $children = UserFactory::new()->count(3)->create(['parent_id' => $parent->id]);
        $repository = app(UserRepository::class);

        /** @var Collection<int, User> $result */
        $result = $repository->with('parent')->with('children')->all();

        $result->each(function (User $user) {
            $this->assertTrue($user->relationLoaded('parent'));
            $this->assertTrue($user->relationLoaded('children'));
        });
    }
}
