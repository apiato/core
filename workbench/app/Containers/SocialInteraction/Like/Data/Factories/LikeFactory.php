<?php

namespace Workbench\App\Containers\SocialInteraction\Like\Data\Factories;

use Workbench\App\Containers\SocialInteraction\Like\Models\Like;
use Workbench\App\Ship\Parents\Factories\Factory as ParentFactory;

/**
 * @template TModel of Like
 *
 * @extends ParentFactory<TModel>
 */
class LikeFactory extends ParentFactory
{
    /** @var class-string<TModel> */
    protected $model = Like::class;

    public function definition(): array
    {
        return [];
    }
}
