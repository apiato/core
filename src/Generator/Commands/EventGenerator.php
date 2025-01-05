<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\ParentTestCase;
use Apiato\Core\Generator\Printer;
use Apiato\Core\Generator\Traits\HasTestTrait;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;

class EventGenerator extends FileGeneratorCommand
{
    use HasTestTrait;

    public static function getCommandName(): string
    {
        return 'apiato:make:event';
    }

    public static function getCommandDescription(): string
    {
        return 'Create an Event file for a Container';
    }

    public static function getFileType(): string
    {
        return 'event';
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
        return "$this->sectionName/$this->containerName/Events/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $file = new PhpFile();
        $printer = new Printer();

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Events');

        // imports
        $parentEventFullPath = 'App\Ship\Events\StorableEvent';
        $namespace->addUse($parentEventFullPath);
        $storeInDatabaseEventFullPath = 'App\Containers\AppSection\Statistic\Contracts\Events\StoreInDatabaseEvent';
        $namespace->addUse($storeInDatabaseEventFullPath);
        $modelFullPath = 'Illuminate\Database\Eloquent\Model';
        $namespace->addUse($modelFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentEventFullPath)
            ->addImplement($storeInDatabaseEventFullPath);

        // constructor method
        $class->addMethod('__construct')
            ->setPublic()
            ->setBody('parent::__construct($model);')
            ->addPromotedParameter('model')
            ->setType($modelFullPath)
            ->setPublic()
            ->setReadOnly();

        return $printer->printFile($file);
    }

    protected function getTestPath(): string
    {
        return "$this->sectionName/$this->containerName/Tests/Unit/Events/$this->fileName" . 'Test.php';
    }

    protected function getTestContent(): string
    {
        $file = new PhpFile();
        $printer = new Printer();

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\Unit\Events');

        // imports
        $parentUnitTestCaseFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\UnitTestCase';
        $namespace->addUse($parentUnitTestCaseFullPath);
        $coversClassFullPath = 'PHPUnit\Framework\Attributes\CoversClass';
        $namespace->addUse($coversClassFullPath);
        $eventFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Events\\' . $this->fileName;
        $namespace->addUse($eventFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName . 'Test')
            ->addAttribute($coversClassFullPath, [new Literal("$this->fileName::class")])
            ->setFinal()
            ->setExtends($parentUnitTestCaseFullPath);

        // test 1
        $class->addMethod('testEventExtendsExpectedModel')
            ->setPublic()
            ->setBody("
\$this->assertSubclassOf(StorableEvent::class, $this->fileName::class);
\$this->assertSubclassOf(StoreInDatabaseEvent::class, $this->fileName::class);
"
            );
        // test 2
        $class->addMethod('testShouldSetProperty')
            ->setPublic()
            ->setBody("
// \$model = ModelFactory::new()->make();
// \$event = new $this->fileName(\$model);

// \$this->assertSame(\$model, \$event->model);
"
            );

        return $printer->printFile($file);
    }

    protected function getParentTestCase(): ParentTestCase
    {
        return ParentTestCase::UNIT_TEST_CASE;
    }
}
