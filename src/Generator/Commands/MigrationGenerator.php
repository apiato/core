<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MigrationGenerator extends GeneratorCommand implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
        ['tablename', null, InputOption::VALUE_OPTIONAL, 'The name for the database table'],
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:generate:migration';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an "empty" migration file for a Container';
    /**
     * The type of class being generated.
     */
    protected string $fileType = 'Migration';
    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/Data/Migrations/*';
    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{date}_{file-name}';
    /**
     * The name of the stub file.
     */
    protected string $stubName = 'migration.stub';

    public function getUserInputs(): array|null
    {
        $tableName = Str::lower($this->checkParameterOrAsk('tablename', 'Enter the name of the database table', Str::snake(Pluralizer::plural($this->containerName))));

        // Now we need to check if there already exists a "default migration file" for this container!
        // We therefore search for a file that is named "xxxx_xx_xx_xxxxxx_NAME"
        $exists = false;

        $folder = $this->parsePathStructure($this->pathStructure, [
            'section-name' => $this->sectionName,
            'container-name' => $this->containerName,
        ]);
        $folder = $this->getFilePath($folder);
        $folder = rtrim($folder, $this->parsedFileName . '.' . $this->getDefaultFileExtension());

        $migrationName = $this->fileName . '.' . $this->getDefaultFileExtension();

        // Get the content of this folder
        $files = File::allFiles($folder);
        foreach ($files as $file) {
            if (Str::endsWith($file->getFilename(), $migrationName)) {
                $exists = true;
            }
        }

        if ($exists) {
            // There exists a basic migration file for this container
            return null;
        }

        return [
            'path-parameters' => [
                'section-name' => $this->sectionName,
                'container-name' => $this->containerName,
            ],
            'stub-parameters' => [
                '_section-name' => Str::lower($this->sectionName),
                'section-name' => $this->sectionName,
                '_container-name' => Str::lower($this->containerName),
                'container-name' => $this->containerName,
                'class-name' => Str::studly($this->fileName),
                'table-name' => $tableName,
            ],
            'file-parameters' => [
                'date' => Carbon::now()->format('Y_m_d_His'),
                'file-name' => $this->fileName,
            ],
        ];
    }

    /**
     * Get the default file name for this component to be generated.
     */
    public function getDefaultFileName(): string
    {
        return 'create_' . Str::snake(Pluralizer::plural($this->containerName)) . '_table';
    }

    /**
     * Removes "special characters" from a string.
     */
    protected function removeSpecialChars($str): string
    {
        return $str;
    }
}
