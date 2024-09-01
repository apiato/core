<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\Traits\HasTestTrait;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class RequestGenerator extends FileGeneratorCommand
{
    use HasTestTrait;

    protected string $model;

    protected string $stub;

    public static function getCommandName(): string
    {
        return 'apiato:make:request';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Request file for a Container';
    }

    public static function getFileType(): string
    {
        return 'request';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
            ['model', null, InputOption::VALUE_OPTIONAL, 'The model this action is for.'],
            ['stub', null, InputOption::VALUE_OPTIONAL, 'The stub file to load for this generator.'],
        ];
    }

    public function getDefaultFileName(): string
    {
        $isCRUD = in_array($this->stub, ['list', 'find', 'create', 'update', 'delete']);

        if ($isCRUD) {
            return Str::ucfirst($this->stub) . $this->model . 'Request';
        }
        return parent::getDefaultFileName();
    }

    public function askCustomInputs(): void
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
            hint: 'Enter the name of the Model that this request is using its policy.',
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
            hint: 'Different types of requests have different default behaviors.',
        );
    }

    protected function getFilePath(): string
    {
        return "$this->sectionName/$this->containerName/UI/API/Requests/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $file = new \Nette\PhpGenerator\PhpFile();
        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\UI\API\Requests');

        // imports
        $parentRequestFullPath = 'App\Ship\Parents\Requests\Request';
        $namespace->addUse($parentRequestFullPath, 'ParentAction');
        $modelFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Models\\' . $this->model;
        $namespace->addUse($modelFullPath);
        $gateFullPath = 'Illuminate\Contracts\Auth\Access\Gate';
        $namespace->addUse($gateFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentRequestFullPath);

        // properties
        $decodeProperty = $class->addProperty('decode')
            ->setVisibility('protected')
            ->setType('array');
        switch ($this->stub) {
            case 'generic':
            case 'create':
            case 'list':
                $decodeProperty->setValue([]);
                break;
            case 'find':
            case 'update':
            case 'delete':
                $decodeProperty->setValue([
                    Str::snake(Str::lower($this->model)) . '_id',
                ]);
                break;
        }

        $urlParametersProperty = $class->addProperty('urlParameters')
            ->setVisibility('protected')
            ->setType('array');
        switch ($this->stub) {
            case 'generic':
            case 'create':
            case 'list':
                $urlParametersProperty->setValue([]);
                break;
            case 'find':
            case 'update':
            case 'delete':
                $urlParametersProperty->setValue([
                    Str::snake(Str::lower($this->model)) . '_id',
                ]);
                break;
        }

        // rules method
        $rulesMethod = $class->addMethod('rules')
            ->setPublic()
            ->setReturnType('array')
            ->setBody('return [];');

        // authorize method
        $act = Str::replaceLast('Request', '', $this->fileName);
        $act = Str::camel($act);
        $rulesMethod = $class->addMethod('authorize')
            ->setPublic()
            ->setReturnType('bool')
            ->setBody("return \$gate->allows('$act', [$this->model::class]);");
        $rulesMethod->addParameter('gate')->setType($gateFullPath);

        // return the file
        return $file;

        // or use the PsrPrinter for output in accordance with PSR-2 / PSR-12 / PER
        // echo (new Nette\PhpGenerator\PsrPrinter)->printFile($file);
    }

    protected function getTestPath(): string
    {
        return $this->sectionName . '/' . $this->containerName . '/Tests/Unit/UI/API/Requests/' . $this->fileName . 'Test.php';
    }

    protected
    function getTestContent(): string
    {
        $file = new \Nette\PhpGenerator\PhpFile();
        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\Unit\UI\API\Requests');

        // imports
        $parentUnitTestCaseFullPath = "App\Containers\AppSection\\$this->containerName\Tests\UnitTestCase";
        $namespace->addUse($parentUnitTestCaseFullPath);
        $modelFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Models\\' . $this->model;
        $namespace->addUse($modelFullPath);
        $requestFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\UI\API\Requests\\' . $this->fileName;
        $namespace->addUse($requestFullPath);


        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName . 'Test')
            ->setFinal()
            ->setExtends($parentUnitTestCaseFullPath);

        // properties
        $class->addProperty('request')
            ->setVisibility('private')
            ->setType($requestFullPath);

        // test method
        $parameter = '';
        switch ($this->stub) {
            case 'find':
            case 'update':
            case 'delete':
                $parameter = Str::snake(Str::lower($this->model)) . '_id';
                $parameter = "'$parameter',";
                break;
        }

        $act = Str::replaceLast('Request', '', $this->fileName);
        $act = Str::camel($act);

        $testMethod1 = $class->addMethod('testDecode')->setPublic();
        $testMethod1->addBody("
\$this->assertSame([
    $parameter
], \$this->request->getDecodeArray());
");
        $testMethod1->setReturnType('void');

        $testMethod2 = $class->addMethod('testUrlParametersArray')->setPublic();
        $testMethod2->addBody("
\$this->assertSame([
    $parameter
], \$this->request->getUrlParametersArray());
");
        $testMethod2->setReturnType('void');

        $testMethod3 = $class->addMethod('testValidationRules')->setPublic();
        $testMethod3->addBody("
\$rules = \$this->request->rules();

\$this->assertSame([], \$rules);
");
        $testMethod3->setReturnType('void');

        $testMethod4 = $class->addMethod('testAuthorizeMethodGateCall')->setPublic();
        $testMethod4->addBody("
\$request = $this->fileName::injectData();
\$gateMock = \$this->getGateMock('$act', [
    User::class,
]);

\$this->assertTrue(\$request->authorize(\$gateMock));
");
        $testMethod4->setReturnType('void');

        $testMethod5 = $class->addMethod('setup')->setProtected();
        $testMethod5->addBody("
parent::setUp();

\$this->request = new $this->fileName();
");
        $testMethod5->setReturnType('void');

        // return the file
        return $file;
    }
}
