<?php

namespace Tests\Doubles;

use Apiato\Core\Traits\CanOwnTrait;
use Apiato\Core\Traits\HashIdTrait;
use Apiato\Core\Traits\HasResourceKeyTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as LaravelAuthenticatableUser;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends LaravelAuthenticatableUser
{
    use HasApiTokens;
    use Notifiable;
    use HashIdTrait;
    use CanOwnTrait;
    use HasResourceKeyTrait;
    use HasFactory;

    protected $table = 'users';

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

    public function children(): BelongsTo
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
