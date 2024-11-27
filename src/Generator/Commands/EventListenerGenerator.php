<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\Printer;
use Nette\PhpGenerator\PhpFile;

//  TODO: Make this command receive the event name as an argument
//  when `EventGenerator` is implemented
class EventListenerGenerator extends FileGeneratorCommand
{
    public static function getCommandName(): string
    {
        return 'apiato:make:listener';
    }

    public static function getCommandDescription(): string
    {
        return 'Create an Event Listener for a Container';
    }

    public static function getFileType(): string
    {
        return 'listener';
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
        return "$this->sectionName/$this->containerName/Listeners/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $file = new PhpFile();
        $printer = new Printer();

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Listeners');

        // imports
        $parentJobFullPath = 'App\Ship\Parents\Listeners\Listener';
        $namespace->addUse($parentJobFullPath, 'ParentListener');
        $shouldQueueFullPath = 'Illuminate\Contracts\Queue\ShouldQueue';
        $namespace->addUse($shouldQueueFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentJobFullPath)
            ->addImplement($shouldQueueFullPath);

        // constructor method
        $constructorMethod = $class->addMethod('__construct')
            ->setPublic();

        // handle method
        $handleMethod = $class->addMethod('handle')
            ->setPublic()
            ->setReturnType('void')
            ->addParameter('event');

        return $printer->printFile($file);
    }
}
