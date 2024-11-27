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

class ModelGenerator extends FileGeneratorCommand
{
    use HasTestTrait;

    protected string $table;

    protected bool $repository;

    protected bool $factory;

    public static function getCommandName(): string
    {
        return 'apiato:make:model';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Model file for a Container';
    }

    public static function getFileType(): string
    {
        return 'model';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
            ['table', null, InputOption::VALUE_OPTIONAL, 'The name of the table to use for this model.'],
            ['repository', null, InputOption::VALUE_NEGATABLE, 'Generate a repository for this model.'],
            ['factory', null, InputOption::VALUE_NEGATABLE, 'Generate a factory for this model.'],
        ];
    }

    public function getDefaultFileName(): string
    {
        return $this->containerName;
    }

    protected function askCustomInputs(): void
    {
        //        $this->model = $this->checkParameterOrAskTextSuggested(
        //            param: 'model',
        //            label: 'Enter the name of the Model:',
        //            default: $this->containerName,
        //            suggestions: $this->getModelsList(
        //                section: $this->sectionName,
        //                container: $this->containerName,
        //                removeModelPostFix: true,
        //            ),
        //            hint: 'Enter the name of the Model this factory is for.',
        //        );

        $this->table = $this->checkParameterOrAskText(
            param: 'table',
            label: 'Enter the name of the table:',
            default: Str::snake(Str::plural($this->containerName)),
            hint: 'The name of the database table you want to create the model for.',
        );

        $this->repository = $this->checkParameterOrConfirm(
            param: 'repository',
            label: 'Do you want to generate a repository for this model?',
            hint: 'This will generate a repository for this model.',
        );

        $this->factory = $this->checkParameterOrConfirm(
            param: 'factory',
            label: 'Do you want to generate a factory for this model?',
            hint: 'This will generate a factory for this model.',
        );
    }

    protected function getFilePath(): string
    {
        return "$this->sectionName/$this->containerName/Models/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $file = new PhpFile();
        $printer = new Printer();

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Models');

        // imports
        $parentModelFullPath = 'App\Ship\Parents\Models\Model';
        $namespace->addUse($parentModelFullPath, 'ParentModel');

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentModelFullPath);

        // properties
        $class->addProperty('table')
            ->setVisibility('protected')
            ->setValue($this->table);

        $class->addProperty('fillable')
            ->setVisibility('protected')
            ->setValue(new Literal("[\n]"));

        $class->addProperty('hidden')
            ->setVisibility('protected')
            ->setValue(new Literal("[\n]"));

        $class->addProperty('casts')
            ->setVisibility('protected')
            ->setValue(new Literal("[\n]"));

        $class->addMethod('getResourceKey')
            ->setReturnType('string')
            ->setBody("return '$this->fileName';")
            ->setPublic();

        return $printer->printFile($file);
    }

    protected function getTestPath(): string
    {
        return $this->sectionName . '/' . $this->containerName . '/Tests/Unit/Models/' . $this->fileName . 'Test.php';
    }

    protected function getTestContent(): string
    {
        $factoryName = $this->fileName . 'Factory';

        $file = new PhpFile();
        $printer = new Printer();

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\Unit\Models');

        // imports
        $parentUnitTestCaseFullPath = "App\Containers\AppSection\\$this->containerName\Tests\UnitTestCase";
        $namespace->addUse($parentUnitTestCaseFullPath);
        $modelFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Models\\' . $this->fileName;
        $namespace->addUse($modelFullPath);
        $factoryFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Data\Factories\\' . $this->fileName . 'Factory';
        $namespace->addUse($factoryFullPath);
        $coversClassAttributeFullPath = 'PHPUnit\Framework\Attributes\CoversClass';
        $namespace->addUse($coversClassAttributeFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName . 'Test')
            ->setFinal()
            ->setExtends($parentUnitTestCaseFullPath)
            ->addAttribute($coversClassAttributeFullPath, [new Literal("$this->fileName::class")]);

        // test method
        $testMethod1 = $class->addMethod('testUsesCorrectTable')->setPublic();
        $testMethod1->addBody("
\$entity = $factoryName::new()->createOne();
\$table = '$this->table';

\$this->assertSame(\$table, \$entity->getTable());
");
        $testMethod1->setReturnType('void');

        $testMethod2 = $class->addMethod('testHasCorrectFillableFields')->setPublic();
        $testMethod2->addBody("
\$entity = $factoryName::new()->createOne();
\$fillables = [
];

\$this->assertSame(\$fillables, \$entity->getFillable());
");
        $testMethod2->setReturnType('void');

        $testMethod3 = $class->addMethod('testHasCorrectCasts')->setPublic();
        $testMethod3->addBody("
\$entity = $factoryName::new()->createOne();
\$casts = [
    'id' => 'int',
];

\$this->assertSame(\$casts, \$entity->getCasts());
");
        $testMethod3->setReturnType('void');

        $testMethod4 = $class->addMethod('testHasCorrectHiddenFields')->setPublic();
        $testMethod4->addBody("
\$entity = $factoryName::new()->createOne();
\$hidden = [
];

\$this->assertSame(\$hidden, \$entity->getHidden());
");
        $testMethod4->setReturnType('void');

        $testMethod5 = $class->addMethod('testHasCorrectResourceKey')->setPublic();
        $testMethod5->addBody("
\$entity = $factoryName::new()->createOne();

\$this->assertSame('$this->fileName', \$entity->getResourceKey());
");
        $testMethod5->setReturnType('void');

        // return the file
        return $printer->printFile($file);
    }

    protected function runGeneratorCommands(): void
    {
        if ($this->repository) {
            $this->runGeneratorCommand(RepositoryGenerator::class, [
                '--section' => $this->sectionName,
                '--container' => $this->containerName,
                '--model' => $this->fileName,
                '--file' => $this->fileName . 'Repository',
                '--test' => $this->test,
            ]);
        }

        if ($this->factory) {
            $this->runGeneratorCommand(FactoryGenerator::class, [
                '--section' => $this->sectionName,
                '--container' => $this->containerName,
                '--model' => $this->fileName,
                '--file' => $this->fileName . 'Factory',
                '--test' => $this->test,
            ]);
        }

        parent::runGeneratorCommands();
    }

    protected function getParentTestCase(): ParentTestCase
    {
        return ParentTestCase::UNIT_TEST_CASE;
    }
}
