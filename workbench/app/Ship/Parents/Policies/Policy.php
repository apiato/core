<?php

namespace Workbench\App\Ship\Parents\Policies;

use Apiato\Abstract\Policies\Policy as AbstractPolicy;
use Workbench\App\Ship\Contracts\Authorizable;

abstract class Policy extends AbstractPolicy
{
    public function before(Authorizable $authorizable, string $ability): bool|null
    {
        if ($authorizable->hasAdminRole()) {
            return true;
        }

        return null;
    }
}
