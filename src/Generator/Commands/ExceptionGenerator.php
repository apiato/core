<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\Printer;
use Nette\PhpGenerator\PhpFile;
use Symfony\Component\HttpFoundation\Response;

class ExceptionGenerator extends FileGeneratorCommand
{
    public static function getCommandName(): string
    {
        return 'apiato:make:exception';
    }

    public static function getCommandDescription(): string
    {
        return 'Create an Exception for a Container';
    }

    public static function getFileType(): string
    {
        return 'exception';
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
        return "$this->sectionName/$this->containerName/Exceptions/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $file = new PhpFile();
        $printer = new Printer();

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Exceptions');

        // imports
        $parentExceptionFullPath = 'App\Ship\Parents\Exceptions\Exception';
        $namespace->addUse($parentExceptionFullPath, 'ParentException');
        $responseFullPath = 'Symfony\Component\HttpFoundation\Response';
        $namespace->addUse($responseFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentExceptionFullPath);

        // properties
        $class->addProperty('code')
            ->setVisibility('protected')
            ->setValue(Response::HTTP_BAD_REQUEST); //  TODO: find a way to initialize it with `Response::HTTP_BAD_REQUEST` not 400

        $class->addProperty('message')
            ->setVisibility('protected')
            ->setValue('Exception Default Message.');

        return $printer->printFile($file);
    }
}
