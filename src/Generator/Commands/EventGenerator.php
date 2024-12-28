<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\Generator;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class EventGenerator extends Generator implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
        ['model', null, InputOption::VALUE_OPTIONAL, 'The model to generate this Event for'],
        ['stub', null, InputOption::VALUE_OPTIONAL, 'The stub file to load for this generator.'],
        ['listener', null, InputOption::VALUE_OPTIONAL, 'Generate a Listener for this Event?'],
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:generate:event';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Event class and its corresponding Listener';
    /**
     * The type of class being generated.
     */
    protected string $fileType = 'Event';
    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/Events/*';
    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{file-name}';
    /**
     * The name of the stub file.
     */
    protected string $stubName = 'events/generic.stub';

    public function getUserInputs(): array|null
    {
        $model = $this->checkParameterOrAsk('model', 'Enter the name of the Model to generate this Event for', Str::ucfirst($this->containerName));
        $listener = $this->option('listener');
        if (is_null($listener)) {
            $listener = $this->checkParameterOrConfirm('listener', 'Do you want to generate a Listener for this Event?', false);
            if ($listener) {
                $this->call('apiato:generate:listener', [
                    '--section' => $this->sectionName,
                    '--container' => $this->containerName,
                    '--file' => $this->fileName . 'Listener',
                    '--event' => $this->fileName,
                ]);
            }
        }

        $stub = Str::lower($this->option('stub')) ?: 'generic';

        $this->stubName = 'events/' . $stub . '.stub';

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
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }
}
