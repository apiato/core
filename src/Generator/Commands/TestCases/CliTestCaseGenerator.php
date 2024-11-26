<?php

namespace Apiato\Core\Generator\Commands\TestCases;

use Apiato\Core\Generator\FileGeneratorCommand;
use Nette\PhpGenerator\PhpFile;

class CliTestCaseGenerator extends FileGeneratorCommand
{
    protected string|null $fileName = 'CliTestCase';

    public static function getCommandName(): string
    {
        return 'apiato:make:testcase:functional:cli';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Cli TestCase file';
    }

    public static function getFileType(): string
    {
        return 'cli test case';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [];
    }

    protected function askCustomInputs(): void
    {
    }

    protected function getFilePath(): string
    {
        return "$this->sectionName/$this->containerName/Tests/Functional/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $file = new PhpFile();
        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\Functional');

        // imports
        $functionalTestCaseFullPath = "App\Containers\\$this->sectionName\\$this->containerName\Tests\FunctionalTestCase";
        $namespace->addUse($functionalTestCaseFullPath);

        // class
        $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setAbstract()
            ->setExtends($functionalTestCaseFullPath);

        return $file;
    }

    protected function runGeneratorCommands(): void
    {
        $this->runGeneratorCommand(FunctionalTestCaseGenerator::class, [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
        ], silent: true);

        parent::runGeneratorCommands();
    }
}
