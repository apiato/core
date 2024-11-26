<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\ParentTestCase;
use Apiato\Core\Generator\Traits\HasTestTrait;
use Illuminate\Support\Str;
use Nette\PhpGenerator\PhpFile;

class ConfigurationGenerator extends FileGeneratorCommand
{
    use HasTestTrait;

    protected string $table;

    public static function getCommandName(): string
    {
        return 'apiato:make:config';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Configuration file for a Container';
    }

    public static function getFileType(): string
    {
        return 'config';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
        ];
    }

    public function getDefaultFileName(): string
    {
        return Str::camel($this->sectionName) . '-' . Str::lower($this->containerName);
    }

    protected function askCustomInputs(): void
    {
    }

    protected function getFilePath(): string
    {
        return "$this->sectionName/$this->containerName/Configs/$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        return "
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | $this->sectionName Section $this->containerName Container
    |--------------------------------------------------------------------------
    |
    |
    |
    */

];
";
    }

    protected function getTestPath(): string
    {
        return $this->sectionName . '/' . $this->containerName . '/Tests/Unit/Configs/' . $this->containerName . 'ConfigTest.php';
    }

    protected function getTestContent(): string
    {
        $file = new PhpFile();
        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\Unit\Configs');

        // imports
        $parentUnitTestCaseFullPath = "App\Containers\\$this->sectionName\\$this->containerName\Tests\UnitTestCase";
        $namespace->addUse($parentUnitTestCaseFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass($this->containerName . 'ConfigTest')
            ->setFinal()
            ->setExtends($parentUnitTestCaseFullPath);

        // test method
        $testMethod = $class->addMethod('testConfigHasCorrectValues')->setPublic();
        $testMethod->addBody("
\$config = config('$this->fileName');
\$this->assertIsArray(\$config);
// \$this->assertArrayHasKey('some_key', \$config);
");

        $testMethod->setReturnType('void');

        return $file;
    }

    protected function getParentTestCase(): ParentTestCase
    {
        return ParentTestCase::UNIT_TEST_CASE;
    }
}
