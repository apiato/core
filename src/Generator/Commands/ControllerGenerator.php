<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\Traits\HasTestTrait;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;
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
        return $this->getAct() . 'Controller';
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

        $file = new \Nette\PhpGenerator\PhpFile();
        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\UI\API\Controllers');

        // imports
        $jsonResponseFullPath = 'Illuminate\Http\JsonResponse';
        $namespace->addUse($jsonResponseFullPath);
        $parentActionFullPath = 'App\Ship\Parents\Controllers\ApiController';
        $namespace->addUse($parentActionFullPath);
        $requestFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\UI\\API\\Requests\\' . $this->getAct() . 'Request';
        $namespace->addUse($requestFullPath);
        $actionFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Actions\\' . $this->getAct() . 'Action';
        $namespace->addUse($actionFullPath);
        $transformerFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\UI\\API\\Transformers\\' . $model . 'Transformer';
        $namespace->addUse($transformerFullPath);
        $responseFullPath = 'Apiato\Core\Facades\Response';
        $namespace->addUse($responseFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentActionFullPath);

        // invoke method
        $invoke = $class->addMethod('__invoke');
        $invoke->addParameter('request')->setType($requestFullPath);
        $invoke->addParameter('action')->setType($actionFullPath);
        $invoke->setReturnType($jsonResponseFullPath);
        switch ($this->stub) {
            case 'list':
                $invoke->addBody("$$entities = \$action->run();");
                $invoke->addBody(sprintf('return Response::createFrom($%s)->transformWith(%s::class)->ok();', $entities, $model . 'Transformer'));
                break;
            case 'create':
            case 'update':
            case 'find':
                $invoke->addBody("$$entity = \$action->run(\$request);");
                $invoke->addBody(sprintf('return Response::createFrom($%s)->transformWith(%s::class)->ok();', $entity, $model . 'Transformer'));
                break;
            case 'delete':
                $invoke->addBody('$action->run($request);');
                $invoke->addBody('return Response::noContent();');
                break;
        }

        return $file;
    }

    protected function getTestPath(): string
    {
        return $this->sectionName . '/' . $this->containerName . '/Tests/Unit/UI/API/Controllers/' . $this->fileName . 'Test.php';
    }

    protected function getTestContent(): string
    {
        $file = new \Nette\PhpGenerator\PhpFile();
        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\Unit\UI\API\Controllers');

        // imports
        $parentUnitTestCaseFullPath = "App\Containers\AppSection\\$this->containerName\Tests\UnitTestCase";
        $namespace->addUse($parentUnitTestCaseFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName . 'Test')
            ->setFinal()
            ->setExtends($parentUnitTestCaseFullPath);

        // test method
        $testMethod = $class->addMethod('testController')->setPublic();
        $testMethod->addBody('// add your test here');

        $testMethod->setReturnType('void');

        // return the file
        return $file;
    }

    private function getAct(): string
    {
        return ucfirst($this->stub) . ('list' == $this->stub ? ucfirst(Pluralizer::plural($this->containerName)) : ucfirst($this->containerName));
    }
}
