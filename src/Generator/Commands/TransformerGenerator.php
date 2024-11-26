<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\ParentTestCase;
use Apiato\Core\Generator\Traits\HasTestTrait;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class TransformerGenerator extends FileGeneratorCommand
{
    use HasTestTrait;

    protected string $model;

    public static function getCommandName(): string
    {
        return 'apiato:make:transformer';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Transformer file for a Container';
    }

    public static function getFileType(): string
    {
        return 'transformer';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
            ['model', null, InputOption::VALUE_OPTIONAL, 'The model this transformer is for.'],
        ];
    }

    public function getDefaultFileName(): string
    {
        return ucfirst($this->model) . 'Transformer';
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
            hint: 'Enter the name of the Model this transformer is for.',
        );
    }

    protected function getFilePath(): string
    {
        return "$this->sectionName/$this->containerName/UI/API/Transformers/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $file = new \Nette\PhpGenerator\PhpFile();
        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\UI\API\Transformers');

        // imports
        $parentFactoryFullPath = 'App\Ship\Parents\Transformers\Transformer';
        $namespace->addUse($parentFactoryFullPath, 'ParentTransformer');
        $modelFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Models\\' . $this->model;
        $namespace->addUse($modelFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentFactoryFullPath);

        // properties
        $class->addProperty('availableIncludes')
            ->setVisibility('protected')
            ->setType('array')
            ->setValue([]);

        $class->addProperty('defaultIncludes')
            ->setVisibility('protected')
            ->setType('array')
            ->setValue([]);

        // transform method
        $transform = $class->addMethod('transform')
            ->setPublic()
            ->setReturnType('array')
            ->setBody("
return [
    'object' => \$entity->getResourceKey(),
    'id' => \$entity->getHashedKey(),
    // add your resource fields here
    'created_at' => \$entity->created_at,
    'updated_at' => \$entity->updated_at,
    'readable_created_at' => \$entity->created_at->diffForHumans(),
    'readable_updated_at' => \$entity->updated_at->diffForHumans(),
    // 'deleted_at' => \$entity->deleted_at,
];
");
        $transform->addParameter('entity')->setType($modelFullPath);

        return $file;
    }

    protected function getTestPath(): string
    {
        return $this->sectionName . '/' . $this->containerName . '/Tests/Unit/UI/API/Transformers/' . $this->fileName . 'Test.php';
    }

    protected function getTestContent(): string
    {
        $entity = Str::lower($this->model);
        $factoryName = $this->model . 'Factory';

        $file = new \Nette\PhpGenerator\PhpFile();
        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\Unit\UI\API\Transformers');

        // imports
        $parentUnitTestCaseFullPath = "App\Containers\AppSection\\$this->containerName\Tests\UnitTestCase";
        $namespace->addUse($parentUnitTestCaseFullPath);
        $modelFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Models\\' . $this->model;
        $namespace->addUse($modelFullPath);
        $factoryFullPath = "App\Containers\\$this->sectionName\\$this->containerName\Data\Factories\\$factoryName";
        $namespace->addUse($factoryFullPath);
        $transformerFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\UI\API\Transformers\\' . $this->fileName;
        $namespace->addUse($transformerFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName . 'Test')
            ->setFinal()
            ->setExtends($parentUnitTestCaseFullPath);

        // properties
        $class->addProperty('transformer')
            ->setVisibility('private')
            ->setType($transformerFullPath);

        // test methods
        $testMethod1 = $class->addMethod('testCanTransformSingleObject')->setPublic();
        $testMethod1->addBody("
\$$entity = $factoryName::new()->createOne();
\$expected = [
    'object' => \${$entity}->getResourceKey(),
    'id' => \${$entity}->getHashedKey(),
    // add your resource fields here
    'created_at' => \${$entity}->created_at,
    'updated_at' => \${$entity}->updated_at,
    'readable_created_at' => \${$entity}->created_at->diffForHumans(),
    'readable_updated_at' => \${$entity}->updated_at->diffForHumans(),
];

\$transformedResource = \$this->transformer->transform(\$$entity);

\$this->assertEquals(\$expected, \$transformedResource);
");

        $testMethod1->setReturnType('void');

        $testMethod2 = $class->addMethod('testAvailableIncludes')->setPublic();
        $testMethod2->addBody('
$this->assertSame([
    // add your available includes here
], $this->transformer->getAvailableIncludes());
');

        $testMethod2->setReturnType('void');

        $testMethod3 = $class->addMethod('testDefaultIncludes')->setPublic();
        $testMethod3->addBody('
$this->assertSame([
    // add your default includes here
], $this->transformer->getDefaultIncludes());
');

        $testMethod3->setReturnType('void');

        $setupMethod = $class->addMethod('setUp')->setPublic();
        $setupMethod->addBody("
parent::setUp();
\$this->transformer = new $this->fileName();
");

        $setupMethod->setReturnType('void');

        // return the file
        return $file;
    }

    protected function getParentTestCase(): ParentTestCase
    {
        return ParentTestCase::UNIT_TEST_CASE;
    }
}
