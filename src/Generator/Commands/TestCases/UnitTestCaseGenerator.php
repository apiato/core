<?php

namespace Apiato\Core\Generator\Commands\TestCases;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\Printer;
use Nette\PhpGenerator\PhpFile;

class UnitTestCaseGenerator extends FileGeneratorCommand
{
    protected string|null $fileName = 'UnitTestCase';

    public static function getCommandName(): string
    {
        return 'apiato:make:testcase:unit';
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
        $file = new PhpFile();
        $printer = new Printer();

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests');

        // imports
        $containerTestCaseFullPath = "App\Containers\\$this->sectionName\\$this->containerName\Tests\ContainerTestCase";
        $namespace->addUse($containerTestCaseFullPath);

        // class
        $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setAbstract()
            ->setExtends($containerTestCaseFullPath);

        return $printer->printFile($file);
    }

    protected function runGeneratorCommands(): void
    {
        $this->runGeneratorCommand(ContainerTestCaseGenerator::class, [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
        ], silent: true);

        parent::runGeneratorCommands();
    }
}
