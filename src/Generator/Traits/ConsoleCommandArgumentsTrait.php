<?php

namespace Apiato\Core\Generator\Traits;

use Symfony\Component\Console\Input\InputOption;

trait ConsoleCommandArgumentsTrait
{
    abstract protected static function getCustomCommandArguments(): array;

    protected function getOptions(): array
    {
        return array_merge($this->getGeneralCommandArguments(), $this->getCustomCommandArguments());
    }

    private function getGeneralCommandArguments(): array
    {
        $arguments = [
            ['section', null, InputOption::VALUE_REQUIRED, 'The name of the section'],
            ['container', null, InputOption::VALUE_REQUIRED, 'The name of the container'],
            ['file', null, InputOption::VALUE_REQUIRED, 'The name of the file'],
        ];
        if ($this->isTestable()) {
            $arguments[] = ['test', null, InputOption::VALUE_NEGATABLE, 'Create a test for the file.'];
        }

        return $arguments;
    }
}
