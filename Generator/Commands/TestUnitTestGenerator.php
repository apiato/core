<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class TestUnitTestGenerator extends GeneratorCommand implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     *
     * @var  array
     */
    public $inputs = [
        ['model', null, InputOption::VALUE_OPTIONAL, 'The model this tests is for.'],
        ['stub', null, InputOption::VALUE_OPTIONAL, 'The stub file to load for this generator.'],
        ['event', null, InputOption::VALUE_OPTIONAL, 'The Event to generate tests for'],
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

    /**
     * @return array
     */
    public function getUserInputs()
    {
        $model = $this->option('model');
        $stub = $this->option('stub');
        $event = $this->option('event');

        if ($stub) {
            if ($event) {
                $this->stubName = 'tests/unit/with_event/'. Str::lower($stub) . '.stub';
            } else {
                $this->stubName = 'tests/unit/' . Str::lower($stub) . '.stub';
            }
        }

        $model = $model ?? $this->containerName;
        $models = Str::plural($model);

        // We need to generate the TestCase class before
        $this->call('apiato:generate:test:testcase', [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
            '--file' => 'TestCase',
            '--ui' => 'generic',
        ]);

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
                '_model' => Str::lower($model),
                'models' => $models,
                '_models' => Str::lower($models),
                'event' => $event,
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
