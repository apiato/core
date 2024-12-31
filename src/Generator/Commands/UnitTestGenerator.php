<?php

namespace Apiato\Generator\Commands;

use Apiato\Generator\Generator;
use Apiato\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class UnitTestGenerator extends Generator implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
        ['model', null, InputOption::VALUE_OPTIONAL, 'The model this tests is for.'],
        ['stub', null, InputOption::VALUE_OPTIONAL, 'The stub file to load for this generator.'],
        ['event', null, InputOption::VALUE_OPTIONAL, 'The Event to generate tests for'],
        ['tablename', null, InputOption::VALUE_OPTIONAL, 'The DB Table to generate tests for'],
        ['foldername', null, InputOption::VALUE_OPTIONAL, 'The folder name to create the test in'],
        ['stubfoldername', null, InputOption::VALUE_OPTIONAL, 'The folder name to load the stub from'],
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:generate:test:unit';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Unit Test file.';
    /**
     * The type of class being generated.
     */
    protected string $fileType = 'Unit Test';
    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/Tests/Unit/*';
    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{file-name}';
    /**
     * The name of the stub file.
     */
    protected string $stubName = 'tests/unit/generic.stub';

    public function getUserInputs(): array|null
    {
        $folderName = $this->checkParameterOrAsk('foldername', 'Enter the folder name to create the test in');
        if ($folderName) {
            $this->pathStructure = '{section-name}/{container-name}/Tests/Unit/' . $folderName . '/*';
        }

        $model = $this->option('model');
        $stub = $this->option('stub');
        $event = $this->option('event');
        $tableName = $this->option('tablename');
        $stubFolderName = $this->option('stubfoldername');

        if ($stub) {
            $stubBasePath = 'tests/unit';
            if ($stubFolderName) {
                $stubBasePath = 'tests/unit/' . $stubFolderName;
            }

            if ($event) {
                $this->stubName = $stubBasePath . '/with_event/' . Str::lower($stub) . '.stub';
            } else {
                $this->stubName = $stubBasePath . '/' . Str::lower($stub) . '.stub';
            }
        }

        $model = $model ?? $this->containerName;
        $models = Str::plural($model);

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
                'class-name' => $this->fileName,
                'model' => $model,
                '_model' => Str::camel($model),
                'models' => $models,
                '_models' => Str::lower($models),
                'event' => $event,
                'table-name' => $tableName,
                '_table-name_' => Str::studly($tableName),
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }

    public function getDefaultFileName(): string
    {
        return 'DefaultUnitTest';
    }
}
