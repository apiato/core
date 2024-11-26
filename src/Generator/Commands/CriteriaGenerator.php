<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\ParentTestCase;
use Apiato\Core\Generator\Traits\HasTestTrait;

class CriteriaGenerator extends FileGeneratorCommand
{
    use HasTestTrait;

    public static function getCommandName(): string
    {
        return 'apiato:make:criteria';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Criteria file for a Container';
    }

    public static function getFileType(): string
    {
        return 'criteria';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
        ];
    }

    public function askCustomInputs(): void
    {
    }

    protected function getFilePath(): string
    {
        return "$this->sectionName/$this->containerName/Data/Criterias/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        $file = new \Nette\PhpGenerator\PhpFile();
        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Data\Criterias');

        // imports
        $parentCriteriaFullPath = 'App\Ship\Parents\Criterias\Criteria';
        $namespace->addUse($parentCriteriaFullPath, 'ParentCriteria');
        $repositoryInterface = 'Prettus\Repository\Contracts\RepositoryInterface';
        $namespace->addUse($repositoryInterface);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName)
            ->setExtends($parentCriteriaFullPath);

        // apply method
        $applyMethod = $class->addMethod('apply')
            ->setPublic()
            ->setReturnType('mixed')
            ->setBody('return $model;');
        $applyMethod->addParameter('model');
        $applyMethod->addParameter('repository')->setType($repositoryInterface);

        // return the file
        return $file;
    }

    protected function getTestPath(): string
    {
        return $this->sectionName . '/' . $this->containerName . '/Tests/Unit/Data/Criterias/' . $this->fileName . 'Test.php';
    }

    protected function getTestContent(): string
    {
        $file = new \Nette\PhpGenerator\PhpFile();
        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\Unit\Data\Criterias');

        // imports
        $parentUnitTestCaseFullPath = "App\Containers\AppSection\\$this->containerName\Tests\UnitTestCase";
        $namespace->addUse($parentUnitTestCaseFullPath);
        $criteriaFullPath = 'App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Data\Criterias\\' . $this->fileName;
        $namespace->addUse($criteriaFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->fileName . 'Test')
            ->setFinal()
            ->setExtends($parentUnitTestCaseFullPath);

        $testMethod = $class->addMethod('testCriteria')->setPublic();
        $testMethod->addBody("
\$criteria = app($this->fileName::class);

// Remove the following lines and add your logic and assertions
\$this->assertInstanceOf($this->fileName::class, \$criteria);
");
        $testMethod->setReturnType('void');

        // return the file
        return $file;
    }

    protected function getParentTestCase(): ParentTestCase
    {
        return ParentTestCase::UNIT_TEST_CASE;
    }
}
