<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\ParentTestCase;
use Apiato\Core\Generator\Printer;
use Apiato\Core\Generator\Traits\HasTestTrait;
use Illuminate\Support\Str;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Symfony\Component\Console\Input\InputOption;

class FactoryGenerator extends FileGeneratorCommand
{
    use HasTestTrait;

    protected string $model;

    public static function getCommandName(): string
    {
        return 'apiato:make:factory';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Factory for a model';
    }

    public static function getFileType(): string
    {
        return 'factory';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
            ['model', null, InputOption::VALUE_OPTIONAL, 'The model this factory is for.'],
        ];
    }

    public function getDefaultFileName(): string
    {
        return ucfirst($this->model) . 'Factory';
    }

    protected function askCustomInputs(): void
    {
        $this->model = $this->checkParameterOrAskTextSuggested(
            param: 'model',
            label: 'Enter the name of the Model:',
            default: $this->containerName,
            suggestions: $this->getModelsList(
                section: $this->sectionName,
                container: $this->containerName,
                removeModelPostFix: true,
            ),
            hint: 'Enter the name of the Model this factory is for.',
        );
    }

    protected function getFilePath(): string
    {
        return "$this->sectionName/$this->containerName/Data/Factories/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $file = new PhpFile();
        $printer = new Printer();

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Data\Factories');

        // imports
        $parentFactoryFullPath = 'App\Ship\Parents\Factories\Factory';
        $namespace->addUse($parentFactoryFullPath, 'ParentFactory');
        $modelFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Models\\' . $this->model;
        $namespace->addUse($modelFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentFactoryFullPath);

        // properties
        $class->addProperty('model')
            ->setVisibility('protected')
            ->setValue(new Literal("$this->model::class"));

        // definition method
        $definition = $class->addMethod('definition')
            ->setPublic()
            ->setReturnType('array')
            ->setBody('return [];');

        return $printer->printFile($file);
    }

    protected function getTestPath(): string
    {
        return $this->sectionName . '/' . $this->containerName . '/Tests/Unit/Data/Factories/' . $this->fileName . 'Test.php';
    }

    protected function getTestContent(): string
    {
        $entity = Str::lower($this->model);

        $file = new PhpFile();
        $printer = new Printer();

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\Unit\Data\Factories');

        // imports
        $parentUnitTestCaseFullPath = "App\Containers\AppSection\\$this->containerName\Tests\UnitTestCase";
        $namespace->addUse($parentUnitTestCaseFullPath);
        $modelFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Models\\' . $this->model;
        $namespace->addUse($modelFullPath);
        $factoryFullPath = "App\Containers\\$this->sectionName\\$this->containerName\Data\Factories\\$this->fileName";
        $namespace->addUse($factoryFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName . 'Test')
            ->setFinal()
            ->setExtends($parentUnitTestCaseFullPath);

        // test method
        $testMethod = $class->addMethod("testCanCreate$this->model")->setPublic();
        $testMethod->addBody("
\$$entity = $this->fileName::new()->createOne();

\$this->assertInstanceOf($this->model::class, $$entity);
");

        $testMethod->setReturnType('void');

        // return the file
        return $printer->printFile($file);
    }

    protected function getParentTestCase(): ParentTestCase
    {
        return ParentTestCase::UNIT_TEST_CASE;
    }
}
