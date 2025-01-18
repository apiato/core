<?php

namespace Workbench\App\Containers\Identity\User\Data\Factories;

use Orchestra\Testbench\Factories\UserFactory as TestbenchUserFactory;
use Workbench\App\Containers\Identity\User\Models\User;

/**
 * @template TModel of User
 *
 * @extends TestbenchUserFactory<TModel>
 */
class UserFactory extends TestbenchUserFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = User::class;

    public function withParent(): static
    {
        return $this->state(fn(array $attributes): array => [
            'parent_id' => static::new()->createOne()->id,
        ]);
    }

    public function withChildren(int $count = 1): static
    {
        return $this->afterCreating(function (User $user) use ($count): void {
            static::new()->count($count)->create([
                'parent_id' => $user->id,
            ]);
        });
    }
}
