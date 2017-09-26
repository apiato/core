<?php

namespace Apiato\Core\Generator\Commands\Container;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;

/**
 * Class ContainerMainServiceProviderGenerator
 *
 * @author  Johannes Schobel <johannes.schobel@googlemail.com>
 */
class ContainerMainServiceProviderGenerator extends GeneratorCommand implements ComponentsGenerator
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:container-mainserviceprovider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the MainServiceProvider for a Container';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $fileType = 'ServiceProvider';

    /**
     * The structure of the file path.
     *
     * @var  string
     */
    protected $pathStructure = '{container-name}/Providers/*';

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
    protected $stubName = 'container/mainserviceprovider.stub';

    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     *
     * @var  array
     */
    public $inputs = [
    ];

    /**
     * urn mixed|void
     */
    public function getUserInputs()
    {
        return [
            'path-parameters' => [
                'container-name' => $this->containerName,
            ],
            'stub-parameters' => [
                '_container-name' => Str::lower($this->containerName),
                'container-name' => $this->containerName,
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
        return 'MainServiceProvider';
    }
}
