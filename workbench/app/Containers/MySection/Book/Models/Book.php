<?php

declare(strict_types=1);

namespace Workbench\App\Containers\MySection\Book\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Ship\Parents\Models\Model as ParentModel;

class Book extends ParentModel
{
    protected $fillable = [
        'author_id',
        'title',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }
}
