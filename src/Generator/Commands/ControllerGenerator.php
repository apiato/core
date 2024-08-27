<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\Traits\HasTestTrait;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ControllerGenerator extends FileGeneratorCommand
{
    use HasTestTrait;

    public static function getFileType(): string
    {
        // TODO: Implement getFileType() method.
    }

    public static function getCommandName(): string
    {
        // TODO: Implement getCommandName() method.
    }

    public static function getCommandDescription(): string
    {
        // TODO: Implement getCommandDescription() method.
    }

    protected static function getCustomCommandArguments(): array
    {
        // TODO: Implement getCustomCommandArguments() method.
    }

    protected function getFilePath(): string
    {
        // TODO: Implement getFilePath() method.
    }

    protected function getFileContent(): string
    {
        // TODO: Implement getFileContent() method.
    }

    protected function askCustomInputs(): void
    {
        // TODO: Implement askCustomInputs() method.
    }

    protected function getTestPath(): string
    {
        // TODO: Implement getTestPath() method.
    }

    protected function getTestContent(): string
    {
        // TODO: Implement getTestContent() method.
    }
}
