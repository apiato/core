<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class EventGenerator extends GeneratorCommand implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     *
     * @var  array
     */
    public $inputs = [
        ['model', null, InputOption::VALUE_OPTIONAL, 'The model to generate this Event for'],
        ['handler', null, InputOption::VALUE_OPTIONAL, 'Generate a Handler for this Event?'],
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
    protected $description = 'Create a new Event class and its corresponding Handler';
    /**
     * The type of class being generated.
     */
    protected string $fileType = 'Event';
    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/Events/Events/*';
    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{file-name}';
    /**
     * The name of the stub file.
     */
    protected string $stubName = 'events/event.stub';

    /**
     * @return array
     */
    public function getUserInputs()
    {
        $model = $this->checkParameterOrAsk('model', 'Enter the name of the Model to generate this Event for', Str::ucfirst($this->containerName));

        $handler = $this->checkParameterOrConfirm('handler', 'Do you want to generate a Handler for this Event?', true);
        if ($handler) {
            // We need to generate a corresponding handler
            // so call the other command
            $status = $this->call('apiato:generate:eventhandler', [
                '--section' => $this->sectionName,
                '--container' => $this->containerName,
                '--file' => $this->fileName . 'Handler',
                '--event' => $this->fileName
            ]);

            if ($status == 0) {
                $this->printInfoMessage('The Handler for Event was successfully generated');
            } else {
                $this->printErrorMessage('Could not generate the corresponding Handler!');
            }
        }

        $this->printInfoMessage('!!! Do not forget to register the Event and/or EventHandler !!!');

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
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }
}
