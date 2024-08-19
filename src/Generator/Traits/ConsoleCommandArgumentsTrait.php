<?php

namespace Apiato\Core\Generator\Traits;

use Symfony\Component\Console\Input\InputOption;

trait ConsoleCommandArgumentsTrait
{
    private function getGeneralCommandArguments(): array
    {
        return [
            ['section', null, InputOption::VALUE_REQUIRED, 'The name of the section'],
            ['container', null, InputOption::VALUE_REQUIRED, 'The name of the container'],
            ['file', null, InputOption::VALUE_REQUIRED, 'The name of the file'],
        ];
    }

    protected abstract static function getCustomCommandArguments(): array;

    protected function getOptions(): array
    {
        return array_merge($this->getGeneralCommandArguments(), $this->getCustomCommandArguments());
    }
}
