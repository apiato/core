<?php

namespace Apiato\Core\Generator;

abstract class CompositeGeneratorCommand extends GeneratorCommand
{
    public function handle(): void
    {
        parent::handle();

        $this->askSection();

        $this->askContainer();

        $this->askCustomInputs();

        $this->runGeneratorCommands();
    }
}
