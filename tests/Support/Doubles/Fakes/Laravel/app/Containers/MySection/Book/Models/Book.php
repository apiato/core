<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\Identity\User\Models\User;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Models\Model as ParentModel;

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
