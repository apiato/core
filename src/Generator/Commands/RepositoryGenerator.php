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

class RepositoryGenerator extends FileGeneratorCommand
{
    use HasTestTrait;

    protected string $model;

    public static function getCommandName(): string
    {
        return 'apiato:make:repository';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Repository file for a Container';
    }

    public static function getFileType(): string
    {
        return 'repository';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
            ['model', null, InputOption::VALUE_OPTIONAL, 'The model this repository is for.'],
        ];
    }

    public function getDefaultFileName(): string
    {
        return ucfirst($this->model) . 'Repository';
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
            hint: 'Enter the name of the Model this repository is for.',
        );
    }

    protected function getFilePath(): string
    {
        return "$this->sectionName/$this->containerName/Data/Repositories/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $file = new PhpFile();
        $printer = new Printer();

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Data\Repositories');

        // imports
        $parentRepositoryFullPath = 'App\Ship\Parents\Repositories\Repository';
        $namespace->addUse($parentRepositoryFullPath, 'ParentRepository');
        $modelFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Models\\' . $this->model;
        $namespace->addUse($modelFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentRepositoryFullPath);

        // properties
        $class->addProperty('model')
            ->setVisibility('protected')
            ->setValue(new Literal("$this->model::class"));
        $class->addProperty('fieldSearchable')
            ->setVisibility('protected')
            ->setValue(['id' => '=']);

        return $printer->printFile($file);
    }

    protected function getTestPath(): string
    {
        return $this->sectionName . '/' . $this->containerName . '/Tests/Unit/Data/Repositories/' . $this->fileName . 'Test.php';
    }

    protected function getTestContent(): string
    {
        $entity = Str::lower($this->model);

        $file = new PhpFile();
        $printer = new Printer();

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\Unit\Data\Repositories');

        // imports
        $parentUnitTestCaseFullPath = "App\Containers\AppSection\\$this->containerName\Tests\UnitTestCase";
        $namespace->addUse($parentUnitTestCaseFullPath);
        $modelFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Models\\' . $this->model;
        $namespace->addUse($modelFullPath);
        $classFullPath = "App\Containers\\$this->sectionName\\$this->containerName\Data\Repositories\\$this->fileName";
        $namespace->addUse($classFullPath);
        $coversClassFullPath = 'PHPUnit\Framework\Attributes\CoversClass';
        $namespace->addUse($coversClassFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName . 'Test')
            ->addAttribute($coversClassFullPath, [new Literal("$this->fileName::class")])
            ->setFinal()
            ->setExtends($parentUnitTestCaseFullPath);

        // test method 1
        $testMethod1 = $class->addMethod('testRepositoryHasExpectedSearchableFieldsSet')->setPublic();
        $testMethod1->addBody("
\$data = [
    'id' => '=',
];
\$repository = app($this->fileName::class);

\$this->assertSame(\$data, \$repository->getFieldsSearchable());
");

        $testMethod1->setReturnType('void');

        // test method 2
        $testMethod2 = $class->addMethod('testReturnsCorrectModel')->setPublic();
        $testMethod2->addBody("
\$repository = app($this->fileName::class);

\$this->assertSame($this->model::class, \$repository->model());
");

        // return the file
        return $printer->printFile($file);
    }

    protected function getParentTestCase(): ParentTestCase
    {
        return ParentTestCase::UNIT_TEST_CASE;
    }
}
