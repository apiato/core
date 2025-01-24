<?php

namespace Workbench\App\Ship\Parents\Models;

use Apiato\Abstract\Models\UserModel as AbstractUserModel;
use Illuminate\Notifications\Notifiable;

abstract class UserModel extends AbstractUserModel
{
    use Notifiable;
}
