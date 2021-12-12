<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class RouteGenerator extends GeneratorCommand implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     *
     * @var  array
     */
    public $inputs = [
        ['ui', null, InputOption::VALUE_OPTIONAL, 'The user-interface to generate the Controller for.'],
        ['operation', null, InputOption::VALUE_OPTIONAL, 'The operation from the Controller to be called (e.g., index)'],
        ['doctype', null, InputOption::VALUE_OPTIONAL, 'The type of the endpoint (private, public)'],
        ['docversion', null, InputOption::VALUE_OPTIONAL, 'The version of the endpoint (1, 2, ...)'],
        ['url', null, InputOption::VALUE_OPTIONAL, 'The URI of the endpoint (/stores, /cars, ...)'],
        ['verb', null, InputOption::VALUE_OPTIONAL, 'The HTTP verb of the endpoint (GET, POST, ...)'],
        ['controller', null, InputOption::VALUE_OPTIONAL, 'The controller used in this route'],
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:generate:route';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Route class';
    /**
     * The type of class being generated.
     */
    protected string $fileType = 'Route';
    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/UI/{user-interface}/Routes/*';
    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{endpoint-name}.{endpoint-version}.{documentation-type}';
    /**
     * The name of the stub file.
     */
    protected string $stubName = 'routes/generic.stub';

    /**
     * @return  array
     */
    public function getUserInputs()
    {
        $ui = Str::lower($this->checkParameterOrChoice('ui', 'Select the UI for the controller', ['API', 'WEB'], 0));
        $version = $this->checkParameterOrAsk('docversion', 'Enter the endpoint version (integer)', 1);
        $doctype = $this->checkParameterOrChoice('doctype', 'Select the type for this endpoint', ['private', 'public'], 0);
        $operation = $this->checkParameterOrAsk('operation', 'Enter the name of the controller function that needs to be invoked when calling this endpoint');
        $verb = Str::upper($this->checkParameterOrAsk('verb', 'Enter the HTTP verb of this endpoint (GET, POST,...)'));
        // Get the URI and remove the first trailing slash
        $url = Str::lower($this->checkParameterOrAsk('url', 'Enter the endpoint URI (foo/bar/{id})'));
        $url = ltrim($url, '/');

        $controllerName = $this->checkParameterOrAsk('controller', 'Enter the controller name', Str::studly($operation) . 'Controller');

        $docUrl = preg_replace('~{(.+?)}~', ':$1', $url);

        $routeName = Str::lower($ui . '_' . $this->containerName . '_' . Str::snake($operation));

        // Change the stub to the currently selected UI (API / WEB)
        $this->stubName = 'routes/' . $ui . '.stub';

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
                'operation' => $operation,
                'doc-api-name' => Str::studly($operation),
                'user-interface' => Str::upper($ui),
                'endpoint-url' => $url,
                'doc-endpoint-url' => '/v' . $version . '/' . $docUrl,
                'endpoint-version' => $version,
                'http-verb' => Str::lower($verb),
                'doc-http-verb' => Str::upper($verb),
                'route-name' => $routeName,
                'auth-middleware' => Str::lower($ui),
                'controller-name' => $controllerName,
            ],
            'file-parameters' => [
                'endpoint-name' => $this->fileName,
                'endpoint-version' => 'v' . $version,
                'documentation-type' => $doctype,
            ],
        ];
    }
}
