<?php

namespace Tests\Support;

use Orchestra\Testbench\Factories\UserFactory as TestbenchUserFactory;

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
        return $this->state(function (array $attributes) {
            return [
                'parent_id' => static::new()->createOne()->id,
            ];
        });
    }

    public function withChildren(int $count = 1): static
    {
        return $this->afterCreating(function (User $user) use ($count) {
            static::new()->count($count)->create([
                'parent_id' => $user->id,
            ]);
        });
    }
}
