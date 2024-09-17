<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\Traits\HasTestTrait;
use Illuminate\Support\Str;
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
        $file = new \Nette\PhpGenerator\PhpFile();
        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Actions');
        $isCRUD = in_array($this->stub, ['list', 'find', 'create', 'update', 'delete']);

        // imports
        $parentActionFullPath = 'App\Ship\Parents\Actions\Action';
        $namespace->addUse($parentActionFullPath, 'ParentAction');
        if ($isCRUD) {
            if (in_array($this->stub, ['create', 'update', 'find'])) {
                $modelFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Models\\' . $this->model;
                $namespace->addUse($modelFullPath);
            } elseif ('list' === $this->stub) {
                $lengthAwarePaginatorFullPath = '\Illuminate\Contracts\Pagination\LengthAwarePaginator';
                $namespace->addUse($lengthAwarePaginatorFullPath);
            }
            $taskFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tasks\\' . Str::ucfirst($this->stub) . $this->model . ('list' === $this->stub ? 's' : '') . 'Task';
            $namespace->addUse($taskFullPath);
            $requestFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\UI\\' . $this->ui . '\Requests\\' . Str::ucfirst($this->stub) . $this->model . ('list' === $this->stub ? 's' : '') . 'Request';
            $namespace->addUse($requestFullPath);
        }

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentActionFullPath);

        // constructor
        $constructor = $class->addMethod('__construct');
        if ($isCRUD) {
            $constructor->addPromotedParameter(Str::lower($this->stub) . $this->model . ('list' === $this->stub ? 's' : '') . 'Task')
                ->setPrivate()
                ->setReadOnly()
                ->setType($taskFullPath);
        }

        // run method
        $runMethod = $class->addMethod('run')->setPublic();
        if ($isCRUD) {
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
                $runMethod->setReturnType($lengthAwarePaginatorFullPath);

                $runMethod->addBody('return $this->?Task->run();', [Str::lower($this->stub) . Str::plural($this->model)]);
            }
        } else {
            $runMethod->setReturnType('void');
        }

        // return the file
        return $file;

        // or use the PsrPrinter for output in accordance with PSR-2 / PSR-12 / PER
        // echo (new Nette\PhpGenerator\PsrPrinter)->printFile($file);
    }

    protected function getTestPath(): string
    {
        return $this->sectionName . '/' . $this->containerName . '/Tests/Unit/Actions/' . $this->fileName . 'Test.php';
    }

    protected function getTestContent(): string
    {
        $file = new \Nette\PhpGenerator\PhpFile();
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
}
