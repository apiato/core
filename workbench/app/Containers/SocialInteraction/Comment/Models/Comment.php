<?php

namespace Workbench\App\Containers\SocialInteraction\Comment\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Workbench\App\Containers\SocialInteraction\Like\Models\Like;
use Workbench\App\Ship\Parents\Models\Model as ParentModel;

class Comment extends ParentModel
{
    protected $fillable = [
        'content',
        'commentable_id',
        'commentable_type',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function likes(): MorphToMany
    {
        return $this->morphToMany(Like::class, 'likable');
    }
}
