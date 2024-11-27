<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\Printer;
use Nette\PhpGenerator\PhpFile;

class JobGenerator extends FileGeneratorCommand
{
    public static function getCommandName(): string
    {
        return 'apiato:make:job';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Job for a Container';
    }

    public static function getFileType(): string
    {
        return 'job';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
        ];
    }

    protected function askCustomInputs(): void
    {
    }

    protected function getFilePath(): string
    {
        return "$this->sectionName/$this->containerName/Jobs/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $file = new PhpFile();
        $printer = new Printer();

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Jobs');

        // imports
        $parentJobFullPath = 'App\Ship\Parents\Jobs\Job';
        $namespace->addUse($parentJobFullPath, 'ParentJob');

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentJobFullPath);

        // constructor method
        $constructorMethod = $class->addMethod('__construct')
            ->setPublic();

        // handle method
        $handleMethod = $class->addMethod('handle')
            ->setPublic()
            ->setReturnType('void');

        return $printer->printFile($file);
    }
}
