<?php

namespace Apiato\Core\Generator\Commands\Container;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ContainerActionGenerator
 *
 * @author  Johannes Schobel <johannes.schobel@googlemail.com>
 */
class ContainerActionGenerator extends GeneratorCommand implements ComponentsGenerator
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:container-action';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Action file for a Container';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $fileType = 'Action';

    /**
     * The structure of the file path.
     *
     * @var  string
     */
    protected $pathStructure = '{container-name}/Actions/*';

    /**
     * The structure of the file name.
     *
     * @var  string
     */
    protected $nameStructure = '{file-name}';

    /**
     * The name of the stub file.
     *
     * @var  string
     */
    protected $stubName = 'action.stub';

    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     *
     * @var  array
     */
    public $inputs = [
        ['model', null, InputOption::VALUE_OPTIONAL, 'The model this action is for.'],
        ['stub', null, InputOption::VALUE_OPTIONAL, 'The stub file to load for this generator.'],
    ];

    /**
     * urn mixed|void
     */
    public function getUserInputs()
    {
        $model = $this->checkParameterOrAsk('model', 'Enter the name of the model this action is for.', $this->containerName);
        $stub = Str::lower($this->checkParameterOrChoice('stub', 'Select the Stub you want to load', ['GetAll', 'GetOne', 'Create', 'Update', 'Delete']));

        // load a new stub-file based on the users choice
        $this->stubName = 'container/actions/' . $stub . '.stub';

        $models = Pluralizer::plural($model);

        $entity = Str::lower($model);
        $entities = Pluralizer::plural($entity);

        return [
            'path-parameters' => [
                'container-name' => $this->containerName,
            ],
            'stub-parameters' => [
                '_container-name' => Str::lower($this->containerName),
                'container-name' => $this->containerName,
                'model' => $model,
                'models' => $models,
                'entity' => $entity,
                'entities' => $entities,
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }

    /**
     * Get the default file name for this component to be generated
     *
     * @return string
     */
    public function getDefaultFileName()
    {
        return 'DefaultAction';
    }
}
