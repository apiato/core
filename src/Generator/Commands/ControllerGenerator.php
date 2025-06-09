<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\ParentTestCase;
use Apiato\Core\Generator\Printer;
use Apiato\Core\Generator\Traits\HasTestTrait;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Symfony\Component\Console\Input\InputOption;

class ControllerGenerator extends FileGeneratorCommand
{
    use HasTestTrait;

    protected string $stub;

    public static function getCommandName(): string
    {
        return 'apiato:make:controller';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Controller file for a Container';
    }

    public static function getFileType(): string
    {
        return 'controller';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
            ['stub', null, InputOption::VALUE_OPTIONAL, 'The stub file to load for this generator.'],
        ];
    }

    public function getDefaultFileName(): string
    {
        return ucfirst($this->stub) . ('list' == $this->stub ? ucfirst(Pluralizer::plural($this->containerName)) : ucfirst($this->containerName)) . 'Controller';
    }

    protected function askCustomInputs(): void
    {
        $this->stub = $this->checkParameterOrSelect(
            param: 'stub',
            label: 'Select the controller type:',
            options: [
                // add generic
                'list' => 'List',
                'find' => 'Find',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
            ],
            default: 'find',
            hint: 'Different types of controllers have different default behaviors.',
        );
    }

    protected function getFilePath(): string
    {
        return "$this->sectionName/$this->containerName/UI/API/Controllers/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        // Name of the model (singular and plural)
        $model = $this->containerName;
        $models = Pluralizer::plural($model);
        $entity = Str::lower($model);
        $entities = Pluralizer::plural($entity);
        $requestName = substr($this->fileName, 0, -10) . 'Request';
        $actionName = substr($this->fileName, 0, -10) . 'Action';

        $file = new PhpFile();
        $printer = new Printer();

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\UI\API\Controllers');

        // imports
        $jsonResponseFullPath = 'Illuminate\Http\JsonResponse';
        $namespace->addUse($jsonResponseFullPath);
        $parentActionFullPath = 'App\Ship\Parents\Controllers\ApiController';
        $namespace->addUse($parentActionFullPath);
        $requestFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\UI\\API\\Requests\\' . $requestName;
        $namespace->addUse($requestFullPath);
        $actionFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Actions\\' . $actionName;
        $namespace->addUse($actionFullPath);
        $transformerFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\UI\\API\\Transformers\\' . $model . 'Transformer';
        $namespace->addUse($transformerFullPath);
        $invalidTransformerExceptionFullPath = 'Apiato\Core\Exceptions\InvalidTransformerException';
        $namespace->addUse($invalidTransformerExceptionFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentActionFullPath);

        // invoke method
        $invoke = $class->addMethod('__invoke');
        $invoke->addComment('@throws InvalidTransformerException');
        $invoke->addParameter('request')->setType($requestFullPath);
        $invoke->addParameter('action')->setType($actionFullPath);
        $invoke->setReturnType($jsonResponseFullPath);
        switch ($this->stub) {
            case 'list':
                $invoke->addBody("$$entities = \$action->run(\$request);");
                $invoke->addBody(sprintf('return $this->transform($%s, %sTransformer::class);', $entities, $model));
                break;
            case 'create':
                $invoke->addBody("$$entity = \$action->transactionalRun(\$request);");
                $invoke->addBody(sprintf('return $this->created($this->transform($%s, %sTransformer::class));', $entity, $model));
                break;
            case 'update':
                $invoke->addBody("$$entity = \$action->transactionalRun(\$request);");
                $invoke->addBody(sprintf('return $this->transform($%s, %sTransformer::class);', $entity, $model));
                break;
            case 'find':
                $invoke->addBody("$$entity = \$action->run(\$request);");
                $invoke->addBody(sprintf('return $this->transform($%s, %sTransformer::class);', $entity, $model));
                break;
            case 'delete':
                $invoke->removeComment();
                $invoke->addBody('$action->transactionalRun($request);');
                $invoke->addBody('return $this->deleted();');
                break;
        }

        return $printer->printFile($file);
    }

    protected function getTestPath(): string
    {
        return $this->sectionName . '/' . $this->containerName . '/Tests/Unit/UI/API/Controllers/' . $this->fileName . 'Test.php';
    }

    protected function getTestContent(): string
    {
        $file = new PhpFile();
        $printer = new Printer();

        $requestName = substr($this->fileName, 0, -10) . 'Request';
        $actionName = substr($this->fileName, 0, -10) . 'Action';
        $runMethod = (in_array($this->stub, ['create', 'update', 'delete'])) ? 'transactionalRun' : 'run';

        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\Unit\UI\API\Controllers');

        // imports
        $parentUnitTestCaseFullPath = "App\Containers\AppSection\\$this->containerName\Tests\UnitTestCase";
        $namespace->addUse($parentUnitTestCaseFullPath);
        $requestFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\UI\\API\\Requests\\' . $requestName;
        $namespace->addUse($requestFullPath);
        $actionFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Actions\\' . $actionName;
        $namespace->addUse($actionFullPath);
        $classFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\UI\API\Controllers\\' . $this->fileName;
        $namespace->addUse($classFullPath);
        $coversClassFullPath = 'PHPUnit\Framework\Attributes\CoversClass';
        $namespace->addUse($coversClassFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName . 'Test')
            ->addAttribute($coversClassFullPath, [new Literal("$this->fileName::class")])
            ->setFinal()
            ->setExtends($parentUnitTestCaseFullPath);

        // test method
        $testMethod = $class->addMethod('testControllerCallsCorrectAction')->setPublic();
        $testMethod->addBody("
\$request = $requestName::injectData();
\$actionSpy = \$this->spy($actionName::class, function (\$mock) {
    \$mock->shouldReceive('run');
});
\$controller = app($this->fileName::class);

\$controller->__invoke(\$request, \$actionSpy);

\$actionSpy->shouldHaveReceived('$runMethod')->once()->with(\$request);
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
