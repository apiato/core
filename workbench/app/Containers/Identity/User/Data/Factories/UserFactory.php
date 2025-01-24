<?php

namespace Workbench\App\Containers\Identity\User\Data\Factories;

use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of User
 *
 * @extends ParentFactory<TModel>
 */
class UserFactory extends ParentFactory
{
    /**
     * @var class-string<TModel>
     */
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => $this->faker->password,
        ];
    }

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
