<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\ParentTestCase;
use Apiato\Core\Generator\Printer;
use Apiato\Core\Generator\Traits\HasTestTrait;
use Nette\PhpGenerator\PhpFile;
use Symfony\Component\Console\Input\InputOption;

class PolicyGenerator extends FileGeneratorCommand
{
    use HasTestTrait;

    protected string $method;

    protected bool $overrideExistingFile = true;

    public static function getCommandName(): string
    {
        return 'apiato:make:policy';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Policy method for a Container';
    }

    public static function getFileType(): string
    {
        return 'policy';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
            ['method', null, InputOption::VALUE_OPTIONAL, 'The method to generate in the policy.'],
        ];
    }

    public function getDefaultFileName(): string
    {
        return ucfirst($this->containerName) . 'Policy';
    }

    protected function askCustomInputs(): void
    {
        $this->method = $this->checkParameterOrAskText(
            param: 'method',
            label: 'Enter the name of the method:',
            default: 'create' . ucfirst($this->containerName),
            hint: 'The name of the method you want to generate in the policy',
        );
    }

    protected function getFilePath(): string
    {
        return "$this->sectionName/$this->containerName/Policies/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $printer = new Printer();

        $fullPath = $this->getFullFilePath($this->getFilePath());
        $fileExists = $this->fileAlreadyExists($fullPath);
        if ($fileExists) {
            $fileContent = file_get_contents($fullPath);
            $file = PhpFile::fromCode($fileContent);

            $namespace = array_values($file->getNamespaces())[0];
            $class = array_values($namespace->getClasses())[0];
        } else {
            $file = new PhpFile();

            $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Policies');

            // imports
            $parentRepositoryFullPath = 'App\Ship\Parents\Policies\Policy';
            $namespace->addUse($parentRepositoryFullPath, 'ParentPolicy');

            // class
            $class = $file->addNamespace($namespace)
                ->addClass($this->fileName)
                ->setExtends($parentRepositoryFullPath);
        }

        // imports
        $userModelFullPath = 'App\Containers\AppSection\User\Models\User';
        $namespace->addUse($userModelFullPath);

        // method
        if (!$class->hasMethod($this->method)) {
            $method = $class->addMethod($this->method)
                ->setPublic()
                ->setBody('
// add your policy logic here
return true;
')
                ->setReturnType('bool');
            $method->addParameter('user')->setType($userModelFullPath);
        }

        return $printer->printFile($file);
    }

    protected function getTestPath(): string
    {
        return $this->sectionName . '/' . $this->containerName . '/Tests/Unit/Policies/' . $this->fileName . 'Test.php';
    }

    protected function getTestContent(): string
    {
        $printer = new Printer();

        $fullPath = $this->getFullFilePath($this->getTestPath());
        $fileExists = $this->fileAlreadyExists($fullPath);

        if ($fileExists) {
            $fileContent = file_get_contents($fullPath);
            $file = PhpFile::fromCode($fileContent);

            $namespace = array_values($file->getNamespaces())[0];
            $class = array_values($namespace->getClasses())[0];
        } else {
            $file = new PhpFile();
            $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\Unit\Policies');

            // imports
            $parentUnitTestCaseFullPath = "App\Containers\AppSection\\$this->containerName\Tests\UnitTestCase";
            $namespace->addUse($parentUnitTestCaseFullPath);
            $policyFullPath = "App\Containers\\$this->sectionName\\$this->containerName\Policies\\$this->fileName";
            $namespace->addUse($policyFullPath);
            $userFactoryFullPath = "App\Containers\\$this->sectionName\\$this->containerName\Data\Factories\UserFactory";
            $namespace->addUse($userFactoryFullPath);

            // class
            $class = $file->addNamespace($namespace)
                ->addClass($this->fileName . 'Test')
                ->setFinal()
                ->setExtends($parentUnitTestCaseFullPath);
        }

        // test method
        $testMethodName = 'testCan' . ucfirst($this->method);
        if (!$class->hasMethod($testMethodName)) {
            $testMethod = $class->addMethod($testMethodName)->setPublic();
            $testMethod->addBody("
\$policy = app($this->fileName::class);
\$user = UserFactory::new()->createOne();

\$this->assertTrue(\$policy->$this->method(\$user));
");

            $testMethod->setReturnType('void');
        }

        // return the file
        return $printer->printFile($file);
    }

    protected function getParentTestCase(): ParentTestCase
    {
        return ParentTestCase::UNIT_TEST_CASE;
    }
}
