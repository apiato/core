<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Models;

use Apiato\Abstract\Models\UserModel as AbstractUserModel;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Contracts\Authorizable;

abstract class UserModel extends AbstractUserModel implements Authorizable
{
    use Notifiable;
    use HasApiTokens;
    use HasRoles;
}
