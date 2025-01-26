<?php

namespace Apiato\Abstract\Actions;

use Illuminate\Support\Facades\DB;

abstract class Action
{
    public function transactionalRun(...$arguments)
    {
        return DB::transaction(fn () => static::run(...$arguments));
    }
}
