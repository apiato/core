<?php

namespace Apiato\Abstract\Actions;

use Illuminate\Support\Facades\DB;
use Webmozart\Assert\Assert;

abstract class Action
{
    /**
     * Calls run() wrapped in a DB transaction.
     */
    public function transactionalRun(...$args): mixed
    {
        // we assert that run method exists
        Assert::methodExists($this, 'run');

        return DB::transaction(fn () => static::run(...$args));
    }
}
