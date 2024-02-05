<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class TestFunctionalTestGenerator extends GeneratorCommand implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
        ['ui', null, InputOption::VALUE_OPTIONAL, 'The user-interface to generate the Test for.'],
        ['model', null, InputOption::VALUE_OPTIONAL, 'The model this tests is for.'],
        ['stub', null, InputOption::VALUE_OPTIONAL, 'The stub file to load for this generator.'],
        ['url', null, InputOption::VALUE_OPTIONAL, 'The URL of the endpoint (/stores, /cars, ...)'],
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:generate:test:functional';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Functional Test file.';
    /**
     * The type of class being generated.
     */
    protected string $fileType = 'Functional Test';
    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/UI/{user-interface}/Tests/Functional/*';
    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{file-name}';
    /**
     * The name of the stub file.
     */
    protected string $stubName = 'tests/functional/generic.stub';

    public function getUserInputs(): array|null
    {
        $ui = Str::lower($this->checkParameterOrChoice('ui', 'Select the UI for the Test', ['API', 'WEB', 'CLI'], 0));

        $model = $this->option('model');
        $stub = $this->option('stub');
        $url = $this->option('url');

        $this->stubName = $stub ? 'tests/functional/' . Str::lower($stub) . '.stub' : 'tests/functional/' . $ui . '.stub';

        $model = $model ?? $this->containerName;
        $models = Str::plural($model);

        // We need to generate the TestCase class before
        $this->call('apiato:generate:test:testcase', [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
            // $ui will be prepended to this string while creating the file.
            // So the final file name will become something like Api + TestCase => ApiTestCase
            '--file' => 'TestCase',
            '--type' => 'functional',
            '--ui' => $ui,
        ]);

        return [
            'path-parameters' => [
                'section-name' => $this->sectionName,
                'container-name' => $this->containerName,
                'user-interface' => Str::upper($ui),
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
                'url' => $url,
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }

    public function getDefaultFileName(): string
    {
        return 'DefaultFunctionalTest';
    }
}
