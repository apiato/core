<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Models;

use Apiato\Core\Abstracts\Models\UserModel as AbstractUserModel;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Contracts\Authorizable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

abstract class UserModel extends AbstractUserModel implements Authorizable
{
    use Notifiable;
    use HasApiTokens;
    use HasRoles;
}
