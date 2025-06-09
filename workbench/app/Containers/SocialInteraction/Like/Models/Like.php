<?php

declare(strict_types=1);

namespace Workbench\App\Containers\SocialInteraction\Like\Models;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Workbench\App\Containers\SocialInteraction\Comment\Models\Comment;
use Workbench\App\Ship\Parents\Models\Model as ParentModel;

class Like extends ParentModel
{
    public function comments(): MorphToMany
    {
        return $this->morphedByMany(Comment::class, 'likeable');
    }
}
