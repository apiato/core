<?php

namespace Apiato\Core\Abstracts\Actions;

use Illuminate\Support\Facades\DB;

abstract class Action
{
    protected string $ui;

    public function transactionalRun(...$arguments)
    {
        return DB::transaction(function () use ($arguments) {
            return static::run(...$arguments);
        });
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
