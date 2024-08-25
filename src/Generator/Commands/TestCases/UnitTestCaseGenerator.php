<?php

namespace Apiato\Core\Generator\Commands\TestCases;

use Apiato\Core\Generator\FileGeneratorCommand;

class UnitTestCaseGenerator extends FileGeneratorCommand
{
    protected string|null $fileName = 'UnitTestCase';

    public static function getCommandName(): string
    {
        return 'apiato:generate:testcase:unit';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Unit TestCase file';
    }

    public static function getFileType(): string
    {
        return 'unit test case';
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
        return "$this->sectionName/$this->containerName/Tests/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $file = new \Nette\PhpGenerator\PhpFile();
        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests');

        // imports
        $containerTestCaseFullPath = "App\Containers\\$this->sectionName\\$this->containerName\Tests\ContainerTestCase";
        $namespace->addUse($containerTestCaseFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setAbstract()
            ->setExtends($containerTestCaseFullPath);

        return $file;
    }
}
