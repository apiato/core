<?php

namespace Apiato\Core\Traits;

trait StateKeeperTrait
{
    /**
     * Stores Data of any kind during the request life cycle.
     * This helps related Actions can share data from different steps.
     */
    public array $stateKeeperStates = [];

    public function keep(array $data = []): self
    {
        foreach ($data as $key => $value) {
            $this->stateKeeperStates[$key] = $value;
        }

        return $this;
    }

    public function retrieve($key)
    {
        return $this->stateKeeperStates[$key];
    }
}
