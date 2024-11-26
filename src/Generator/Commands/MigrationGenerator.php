<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\FileGeneratorCommand;
use Apiato\Core\Generator\ParentTestCase;
use Apiato\Core\Generator\Traits\HasTestTrait;
use Carbon\Carbon;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;
use Nette\PhpGenerator\PhpFile;
use Symfony\Component\Console\Input\InputOption;

class MigrationGenerator extends FileGeneratorCommand
{
    use HasTestTrait;

    protected string $table;

    public static function getCommandName(): string
    {
        return 'apiato:make:migration';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Migration file for a Container';
    }

    public static function getFileType(): string
    {
        return 'migration';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
            ['table', null, InputOption::VALUE_OPTIONAL, 'The name of the table to create.'],
        ];
    }

    public function getDefaultFileName(): string
    {
        return 'create_' . Str::snake(Pluralizer::plural($this->containerName)) . '_table';
    }

    protected function askCustomInputs(): void
    {
        $this->table = $this->checkParameterOrAskText(
            param: 'table',
            label: 'Enter the name of the table:',
            default: Str::snake(Pluralizer::plural($this->containerName)),
            hint: 'The name of the database table you want to create the migration for',
        );
    }

    protected function getFilePath(): string
    {
        return "$this->sectionName/$this->containerName/Data/Migrations/" . Carbon::now()->format('Y_m_d_His') . "_$this->fileName.php";
    }

    protected function getFileContent(): string
    {
        return "
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('$this->table', function (Blueprint \$table) {
            \$table->id();
            // add your columns here
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('$this->table');
    }
};
";
    }

    protected function getTestPath(): string
    {
        return $this->sectionName . '/' . $this->containerName . '/Tests/Unit/Data/Migrations/MigrationTest.php';
    }

    protected function getTestContent(): string
    {
        $file = new PhpFile();
        $namespace = $file->addNamespace('App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Tests\Unit\Data\Migrations');

        // imports
        $parentUnitTestCaseFullPath = "App\Containers\\$this->sectionName\\$this->containerName\Tests\UnitTestCase";
        $namespace->addUse($parentUnitTestCaseFullPath);
        $coversNothingAttributeFullPath = 'PHPUnit\Framework\Attributes\CoversNothing';
        $namespace->addUse($coversNothingAttributeFullPath);

        // class
        $class = $file->addNamespace($namespace)
            ->addClass('MigrationTest')
            ->setFinal()
            ->setExtends($parentUnitTestCaseFullPath)
            ->addAttribute($coversNothingAttributeFullPath);

        // test method
        $testMethod = $class->addMethod('test' . $this->camelize($this->table) . 'TableHasExpectedColumns')->setPublic();
        $testMethod->addBody("
\$columns = [
'id' => 'bigint',
// add your columns here
'created_at' => 'datetime',
'updated_at' => 'datetime',
];

\$this->assertDatabaseTable('$this->table', \$columns);
");

        $testMethod->setReturnType('void');

        return $file;
    }

    protected function getParentTestCase(): ParentTestCase
    {
        return ParentTestCase::UNIT_TEST_CASE;
    }
}
