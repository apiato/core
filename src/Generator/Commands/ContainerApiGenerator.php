<?php

namespace Apiato\Generator\Commands;

use Apiato\Generator\Generator;
use Apiato\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

final class ContainerApiGenerator extends Generator implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
        ['docversion', null, InputOption::VALUE_OPTIONAL, 'The version of all endpoints to be generated (1, 2, ...)'],
        ['doctype', null, InputOption::VALUE_OPTIONAL, 'The type of all endpoints to be generated (private, public)'],
        ['url', null, InputOption::VALUE_OPTIONAL, 'The base URI of all endpoints (/stores, /cars, ...)'],
        ['controllertype', null, InputOption::VALUE_OPTIONAL, 'The controller type (SAC, MAC)'],
        ['events', null, InputOption::VALUE_OPTIONAL, 'Generate Events for this Container?'],
        ['listeners', null, InputOption::VALUE_OPTIONAL, 'Generate Event Listeners for Events of this Container?'],
        ['tests', null, InputOption::VALUE_OPTIONAL, 'Generate Tests for this Container?'],
        ['maincalled', false, InputOption::VALUE_NONE],
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:make:container:api';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Container for apiato from scratch (API Part)';
    /**
     * The type of class being generated.
     */
    protected string $fileType = 'Container';
    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/*';
    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{file-name}';
    /**
     * The name of the stub file.
     */
    protected string $stubName = 'composer.stub';

    public function getUserInputs(): array|null
    {
        $ui = 'api';

        $sectionName = $this->sectionName;
        $_sectionName = Str::lower($this->sectionName);

        $containerName = $this->containerName;
        $_containerName = Str::lower($this->containerName);

        $model = $this->containerName;
        $models = Pluralizer::plural($model);

        $this->printInfoMessage('Generating README File');
        $this->call('apiato:make:readme', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => 'README',
        ]);

        $this->printInfoMessage('Generating Configuration File');
        $this->call('apiato:make:configuration', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => Str::camel($this->sectionName) . '-' . Str::camel($this->containerName),
        ]);

        $this->printInfoMessage('Generating Model and Repository');
        $this->call('apiato:make:model', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => $model,
            '--repository' => true,
        ]);

        $this->printInfoMessage('Generating a basic Migration file');
        $this->call('apiato:make:migration', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => 'create_' . Str::snake($models) . '_table',
            '--tablename' => Str::snake($models),
        ]);

        $this->printInfoMessage('Generating Transformer for the Model');
        $this->call('apiato:make:transformer', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => $containerName . 'Transformer',
            '--model' => $model,
            '--full' => false,
        ]);

        $this->printInfoMessage('Generating Factory for the Model');
        $this->call('apiato:make:factory', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => $containerName . 'Factory',
            '--model' => $model,
        ]);

        $this->printInfoMessage('Generating Default Routes');
        $version = $this->checkParameterOrAsk('docversion', 'Enter the version for all API endpoints (integer)', 1);
        $doctype = $this->checkParameterOrChoice('doctype', 'Select the type for all API endpoints', ['private', 'public'], 0);

        // get the URI and remove the first trailing slash
        $url = Str::lower($this->checkParameterOrAsk('url', 'Enter the base URI for all API endpoints (foo/bar/{id})', Str::kebab($models)));
        $url = ltrim($url, '/');

        $controllertype = Str::lower($this->checkParameterOrChoice('controllertype', 'Select the controller type (Single or Multi Action Controller)', ['SAC', 'MAC'], 0));

        $generateEvents = $this->checkParameterOrConfirm('events', 'Do you want to generate the corresponding CRUD Events for this Container?', false);
        $generateListeners = false;
        if ($generateEvents) {
            $generateListeners = $this->checkParameterOrConfirm('listeners', 'Do you want to generate the corresponding Event Listeners for this Events?', false);
        }
        $generateTests = $this->checkParameterOrConfirm('tests', 'Do you want to generate the corresponding Tests for this Container?', true);

        $generateEvents ?: $this->printInfoMessage('Generating CRUD Events');
        $generateTests ?: $this->printInfoMessage('Generating Tests for Container');
        $this->printInfoMessage('Generating Requests for Routes');
        $this->printInfoMessage('Generating Default Actions');
        $this->printInfoMessage('Generating Default Tasks');
        $this->printInfoMessage('Generating Default Controller/s');

        $events = [];
        $routes = [
            [
                'stub' => 'List',
                'name' => 'List' . $models,
                'operation' => 'list',
                'verb' => 'GET',
                'url' => $url,
                'action' => 'List' . $models . 'Action',
                'request' => 'List' . $models . 'Request',
                'task' => 'List' . $models . 'Task',
                'unittest' => [
                    'task' => [
                        'stubfoldername' => 'tasks',
                        'foldername' => 'Tasks',
                        'filename' => 'List' . $models . 'TaskTest',
                    ],
                ],
                'functionaltest' => 'List' . $models . 'Test',
                'event' => $models . 'Listed',
                'controller' => 'List' . $models . 'Controller',
            ],
            [
                'stub' => 'Find',
                'name' => 'Find' . $model . 'ById',
                'operation' => 'findById',
                'verb' => 'GET',
                'url' => $url . '/{id}',
                'action' => 'Find' . $model . 'ByIdAction',
                'request' => 'Find' . $model . 'ByIdRequest',
                'task' => 'Find' . $model . 'ByIdTask',
                'unittest' => [
                    'task' => [
                        'stubfoldername' => 'tasks',
                        'foldername' => 'Tasks',
                        'filename' => 'Find' . $model . 'ByIdTaskTest',
                    ],
                ],
                'functionaltest' => 'Find' . $model . 'ByIdTest',
                'event' => $model . 'Requested',
                'controller' => 'Find' . $model . 'ByIdController',
            ],
            [
                'stub' => 'Create',
                'name' => 'Create' . $model,
                'operation' => 'create',
                'verb' => 'POST',
                'url' => $url,
                'action' => 'Create' . $model . 'Action',
                'request' => 'Create' . $model . 'Request',
                'task' => 'Create' . $model . 'Task',
                'unittest' => [
                    'task' => [
                        'stubfoldername' => 'tasks',
                        'foldername' => 'Tasks',
                        'filename' => 'Create' . $model . 'TaskTest',
                    ],
                ],
                'functionaltest' => 'Create' . $model . 'Test',
                'event' => $model . 'Created',
                'controller' => 'Create' . $model . 'Controller',
            ],
            [
                'stub' => 'Update',
                'name' => 'Update' . $model,
                'operation' => 'update',
                'verb' => 'PATCH',
                'url' => $url . '/{id}',
                'action' => 'Update' . $model . 'Action',
                'request' => 'Update' . $model . 'Request',
                'task' => 'Update' . $model . 'Task',
                'unittest' => [
                    'task' => [
                        'stubfoldername' => 'tasks',
                        'foldername' => 'Tasks',
                        'filename' => 'Update' . $model . 'TaskTest',
                    ],
                ],
                'functionaltest' => 'Update' . $model . 'Test',
                'event' => $model . 'Updated',
                'controller' => 'Update' . $model . 'Controller',
            ],
            [
                'stub' => 'Delete',
                'name' => 'Delete' . $model,
                'operation' => 'delete',
                'verb' => 'DELETE',
                'url' => $url . '/{id}',
                'action' => 'Delete' . $model . 'Action',
                'request' => 'Delete' . $model . 'Request',
                'task' => 'Delete' . $model . 'Task',
                'unittest' => [
                    'task' => [
                        'stubfoldername' => 'tasks',
                        'foldername' => 'Tasks',
                        'filename' => 'Delete' . $model . 'TaskTest',
                    ],
                ],
                'functionaltest' => 'Delete' . $model . 'Test',
                'event' => $model . 'Deleted',
                'controller' => 'Delete' . $model . 'Controller',
            ],
        ];

        foreach ($routes as $route) {
            $this->call('apiato:make:request', [
                '--section' => $sectionName,
                '--container' => $containerName,
                '--file' => $route['request'],
                '--ui' => $ui,
                '--stub' => $route['stub'],
            ]);

            $this->call('apiato:make:action', [
                '--section' => $sectionName,
                '--container' => $containerName,
                '--file' => $route['action'],
                '--ui' => $ui,
                '--model' => $model,
                '--stub' => $route['stub'],
            ]);

            $this->call('apiato:make:task', [
                '--section' => $sectionName,
                '--container' => $containerName,
                '--file' => $route['task'],
                '--model' => $model,
                '--stub' => $route['stub'],
                '--event' => $generateEvents ? $route['event'] : false,
            ]);

            if ($generateEvents) {
                $this->call('apiato:make:event', [
                    '--section' => $sectionName,
                    '--container' => $containerName,
                    '--file' => $route['event'],
                    '--model' => $model,
                    '--stub' => $route['stub'],
                    '--listener' => false,
                ]);
                $events[] = $route['event'];
            }

            if ($generateTests) {
                $this->call('apiato:make:test:unit', [
                    '--section' => $sectionName,
                    '--container' => $containerName,
                    '--file' => $route['unittest']['task']['filename'],
                    '--stubfoldername' => $route['unittest']['task']['stubfoldername'],
                    '--foldername' => $route['unittest']['task']['foldername'],
                    '--model' => $model,
                    '--stub' => $route['stub'],
                    '--event' => $generateEvents ? $route['event'] : false,
                ]);

                $this->call('apiato:make:test:unit', [
                    '--section' => $sectionName,
                    '--container' => $containerName,
                    '--file' => $model . 'FactoryTest',
                    '--foldername' => 'Factories',
                    '--model' => $model,
                    '--stub' => 'factory',
                    '--event' => false,
                ]);

                $this->call('apiato:make:test:unit', [
                    '--section' => $sectionName,
                    '--container' => $containerName,
                    '--file' => $models . 'MigrationTest',
                    '--stubfoldername' => 'data',
                    '--foldername' => 'Data/Migrations',
                    '--model' => $model,
                    '--stub' => 'migration',
                    '--event' => false,
                    '--tablename' => Str::snake(Pluralizer::plural($containerName)),
                ]);

                $this->call('apiato:make:test:functional', [
                    '--section' => $sectionName,
                    '--container' => $containerName,
                    '--file' => $route['functionaltest'],
                    '--model' => $model,
                    '--ui' => $ui,
                    '--stub' => $route['stub'],
                    '--url' => $route['url'],
                ]);
            }

            $routeArgs = [
                '--section' => $sectionName,
                '--container' => $containerName,
                '--file' => $route['name'],
                '--ui' => $ui,
                '--operation' => $route['operation'],
                '--doctype' => $doctype,
                '--docversion' => $version,
                '--url' => $route['url'],
                '--verb' => $route['verb'],
            ];

            if ('sac' === $controllertype) {
                $this->call('apiato:make:route', [
                    ...$routeArgs,
                    '--controller' => $route['controller'],
                    '--sac' => true,
                ]);

                $this->call('apiato:make:controller', [
                    '--section' => $sectionName,
                    '--container' => $containerName,
                    '--file' => $route['controller'],
                    '--model' => $model,
                    '--ui' => $ui,
                    '--stub' => $route['stub'],
                ]);
            } else {
                $this->call('apiato:make:route', [
                    ...$routeArgs,
                    '--controller' => 'Controller',
                    '--sac' => false,
                ]);
            }
        }

        if ('mac' === $controllertype) {
            $this->printInfoMessage('Generating Controller to wire everything together');
            $this->call('apiato:make:controller', [
                '--section' => $sectionName,
                '--container' => $containerName,
                '--model' => $model,
                '--file' => 'Controller',
                '--ui' => $ui,
                '--stub' => 'crud',
            ]);
        }

        if ($generateEvents && $generateListeners) {
            $this->printInfoMessage('Generating Event Listeners');
            foreach ($events as $event) {
                $listener = $event . 'Listener';
                $this->call('apiato:make:listener', [
                    '--section' => $this->sectionName,
                    '--container' => $this->containerName,
                    '--file' => $listener,
                    '--event' => $event,
                ]);
            }
        }

        $this->printInfoMessage('Generating ServiceProvider');
        $this->call('apiato:make:provider', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => Str::title($this->containerName) . 'ServiceProvider',
            '--stub' => 'service-provider',
        ]);

        $generateComposerFile = [
            'path-parameters' => [
                'section-name' => $this->sectionName,
                'container-name' => $this->containerName,
            ],
            'stub-parameters' => [
                '_section-name' => $_sectionName,
                'section-name' => $this->sectionName,
                '_container-name' => $_containerName,
                'container-name' => $containerName,
                'class-name' => $this->fileName,
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];

        if (!$this->option('maincalled')) {
            $this->printInfoMessage('Generating Composer File');

            return $generateComposerFile;
        }

        return null;
    }

    public function getDefaultFileName(): string
    {
        return 'composer';
    }

    public function getDefaultFileExtension(): string
    {
        return 'json';
    }
}
