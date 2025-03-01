<?php

namespace Workbench\App\Ship\Parents\Models;

use Apiato\Core\Models\UserModel as AbstractUserModel;
use Illuminate\Notifications\Notifiable;

abstract class UserModel extends AbstractUserModel
{
    use Notifiable;
}
