<?php

namespace Workbench\App\Containers\SocialInteraction\Comment\Data\Factories;

use Workbench\App\Containers\SocialInteraction\Comment\Models\Comment;
use Workbench\App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Comment
 *
 * @extends ParentFactory<TModel>
 */
class CommentFactory extends ParentFactory
{
    /** @var class-string<TModel> */
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'content' => $this->faker->text,
        ];
    }
}
