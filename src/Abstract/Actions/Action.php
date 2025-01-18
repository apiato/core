<?php

namespace Apiato\Abstract\Actions;

use Illuminate\Support\Facades\DB;

abstract class Action
{
    protected string $ui;

    public function transactionalRun(...$arguments)
    {
        return DB::transaction(fn () => static::run(...$arguments));
    }

    public function getUI(): string
    {
        return $this->ui;
    }

    public function setUI(string $interface): static
    {
        $this->ui = $interface;

        return $this;
    }
}
