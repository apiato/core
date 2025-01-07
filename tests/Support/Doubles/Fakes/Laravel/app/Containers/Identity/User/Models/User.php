<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\Identity\User\Models;

use Apiato\Foundation\Support\Traits\Model\ModelTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Models\UserModel as ParentUserModel;

class User extends ParentUserModel
{
    use ModelTrait;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'parent_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'author_id');
    }
}
