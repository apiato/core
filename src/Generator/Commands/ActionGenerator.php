<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\ParentTestCase;
use Apiato\Core\Generator\Traits\HasTestTrait;
use Illuminate\Support\Str;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use Symfony\Component\Console\Input\InputOption;

class ActionGenerator extends FileGeneratorCommand
{
    use HasTestTrait;

    protected string $model;

    protected string $stub;

    protected string $ui;

    public static function getCommandName(): string
    {
        return 'apiato:make:action';
    }

    public static function getCommandDescription(): string
    {
        return 'Create an Action file for a Container';
    }

    public static function getFileType(): string
    {
        return 'action';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
            ['model', null, InputOption::VALUE_OPTIONAL, 'The model this action is for.'],
            ['stub', null, InputOption::VALUE_OPTIONAL, 'The stub file to load for this generator.'],
            ['ui', null, InputOption::VALUE_OPTIONAL, 'The user-interface to generate the Action for.'],
        ];
    }

    public function askCustomInputs(): void
    {
        $this->model = $this->checkParameterOrAskText(
            param: 'model',
            label: 'Enter the name of the Model:',
            default: $this->containerName,
            hint: 'Enter the name of the Model this action is for.',
        );

        $this->ui = $this->checkParameterOrSelect(
            param: 'ui',
            label: 'Which UI is this Action for?',
            options: ['API', 'WEB'],
            default: 'API',
            hint: 'Different UIs have different request/response formats.',
        );

        $this->stub = $this->checkParameterOrSelect(
            param: 'stub',
            label: 'Select the action type:',
            options: [
                'generic' => 'Generic',
                'list' => 'List',
                'find' => 'Find',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
            ],
            default: 'generic',
            hint: 'Different types of actions have different default behaviors.',
        );
    }

    protected function getFilePath(): string
    {
        return "$this->sectionName/$this->containerName/Actions/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $isCRUD = in_array($this->stub, ['list', 'find', 'create', 'update', 'delete']);

        $file = new PhpFile();
        $namespace = $this->addNamespace($file);
        $class = $this->addClass($file, $namespace);
        $this->addConstructor($namespace, $class, $isCRUD);
        $this->addRunMethod($namespace, $class, $isCRUD);

        return (new PsrPrinter())->printFile($file);
    }

    protected function addNamespace(PhpFile $file): PhpNamespace
    {
        return $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Actions');
    }

    protected function addClass(PhpFile $file, PhpNamespace $namespace): ClassType
    {
        $parentActionFullPath = 'App\Ship\Parents\Actions\Action';
        $namespace->addUse($parentActionFullPath, 'ParentAction');

        return $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentActionFullPath);
    }

    protected function addConstructor(PhpNamespace $namespace, ClassType $class, bool $isCRUD): Method
    {
        $taskFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tasks\\' . Str::ucfirst($this->stub) . $this->model . ('list' === $this->stub ? 's' : '') . 'Task';
        $namespace->addUse($taskFullPath);

        $constructor = $class->addMethod('__construct');
        if ($isCRUD) {
            $constructor->addPromotedParameter(Str::lower($this->stub) . $this->model . ('list' === $this->stub ? 's' : '') . 'Task')
                ->setPrivate()
                ->setReadOnly()
                ->setType($taskFullPath);
        }

        return $constructor;
    }

    protected function addRunMethod(PhpNamespace $namespace, ClassType $class, bool $isCRUD): Method
    {
        $runMethod = $class->addMethod('run')->setPublic();
        if ($isCRUD) {
            $requestFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\UI\\' . $this->ui . '\Requests\\' . Str::ucfirst($this->stub) . $this->model . ('list' === $this->stub ? 's' : '') . 'Request';
            $namespace->addUse($requestFullPath);
            $modelFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Models\\' . $this->model;
            $namespace->addUse($modelFullPath);

            $runMethod->addPromotedParameter('request')
                ->setPrivate()
                ->setType($requestFullPath);

            if ('create' === $this->stub) {
                $runMethod->setReturnType($modelFullPath);

                $runMethod->addBody('
$data = $request->sanitizeInput([
    // add your request data here
]);
            ');
                $runMethod->addBody("return \$this->?{$this->model}Task->run(\$data);", [Str::lower($this->stub)]);
            } elseif ('update' === $this->stub) {
                $runMethod->setReturnType($modelFullPath);

                $runMethod->addBody('
$data = $request->sanitizeInput([
    // add your request data here
]);
            ');
                $runMethod->addBody("return \$this->?{$this->model}Task->run(\$data, \$request->id);", [Str::lower($this->stub)]);
            } elseif ('delete' === $this->stub) {
                $runMethod->setReturnType('int');
                $runMethod->addBody("return \$this->?{$this->model}Task->run(\$request->id);", [Str::lower($this->stub)]);
            } elseif ('find' === $this->stub) {
                $runMethod->setReturnType($modelFullPath);
                $runMethod->addBody("return \$this->?{$this->model}Task->run(\$request->id);", [Str::lower($this->stub)]);
            } elseif ('list' === $this->stub) {
                $lengthAwarePaginatorFullPath = '\Illuminate\Contracts\Pagination\LengthAwarePaginator';
                $namespace->addUse($lengthAwarePaginatorFullPath);

                $runMethod->setReturnType($lengthAwarePaginatorFullPath);
                $runMethod->addBody('return $this->?Task->run();', [Str::lower($this->stub) . Str::plural($this->model)]);
            }
        } else {
            $runMethod->setReturnType('void');
        }

        return $runMethod;
    }

    protected function getTestPath(): string
    {
        return $this->sectionName . '/' . $this->containerName . '/Tests/Unit/Actions/' . $this->fileName . 'Test.php';
    }

    protected function getTestContent(): string
    {
        $file = new PhpFile();
        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\Unit\Actions');

        // imports
        $parentUnitTestCaseFullPath = "App\Containers\AppSection\\$this->containerName\Tests\UnitTestCase";
        $namespace->addUse($parentUnitTestCaseFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName . 'Test')
            ->setFinal()
            ->setExtends($parentUnitTestCaseFullPath);

        // test method
        $testMethod = $class->addMethod('testAction')->setPublic();
        $testMethod->addBody('// add your test here');

        $testMethod->setReturnType('void');

        // return the file
        return $file;
    }

    protected function getParentTestCase(): ParentTestCase
    {
        return ParentTestCase::UNIT_TEST_CASE;
    }
}
