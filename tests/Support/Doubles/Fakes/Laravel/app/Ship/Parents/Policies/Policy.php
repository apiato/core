<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Policies;

use Apiato\Abstract\Policies\Policy as AbstractPolicy;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Contracts\Authorizable;

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
