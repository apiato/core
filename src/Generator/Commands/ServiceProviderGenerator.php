<?php

namespace Apiato\Generator\Commands;

use Apiato\Generator\Generator;
use Apiato\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ServiceProviderGenerator extends Generator implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
        ['stub', null, InputOption::VALUE_OPTIONAL, 'The stub file to load for this generator.'],
        ['event-listeners', null, InputOption::VALUE_OPTIONAL, 'The Event Listeners that this Provider should register.'],
        ['event-service-provider', null, InputOption::VALUE_OPTIONAL, 'The Event Service Provider that this Provider should register.'],
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:generate:provider';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Service Provider for a Container';
    /**
     * The type of class being generated.
     */
    protected string $fileType = 'ServiceProvider';
    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/Providers/*';
    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{file-name}';
    /**
     * The name of the stub file.
     */
    protected string $stubName = 'providers/generic.stub';

    private const TAB2 = '        ';
    private const TAB3 = '            ';

    public function getUserInputs(): array|null
    {
        $stub = $this->option('stub');
        $eventServiceProvider = $this->option('event-service-provider');
        if (!$stub) {
            $stub = $this->checkParameterOrChoice(
                'stub',
                'Select the Stub you want to load',
                ['Generic', 'MainServiceProvider', 'EventServiceProvider', 'MiddlewareServiceProvider'],
                0,
            );

            $stub = match ($stub) {
                'MainServiceProvider' => 'main-service-provider',
                'EventServiceProvider' => 'generic-event-service-provider',
                'MiddlewareServiceProvide' => 'middleware-service-provider',
                default => 'generic',
            };
        }
        $this->stubName = "providers/$stub.stub";
        $eventListeners = $this->option('event-listeners');
        $eventListenersString = '[]';
        $listenersUseStatements = '';
        $eventsUseStatements = '';
        if ($eventListeners) {
            $listenersWithClass = array_map(static function ($listeners, $listener) {
                return [$listener . '::class' => array_map(static fn ($event) => $event . '::class', $listeners)];
            }, $eventListeners, array_keys($eventListeners));
            $eventListenersString = '[' . PHP_EOL . array_reduce($listenersWithClass, static function ($carry, $item) {
                $carry .= array_reduce(array_keys($item), static function ($carry, $key) use ($item) {
                    $carry .= self::TAB2 . $key . ' => [' . PHP_EOL;
                    $carry .= array_reduce($item[$key], static function ($carry, $event) {
                        $carry .= self::TAB3 . $event . ',' . PHP_EOL;

                        return $carry;
                    });
                    $carry .= self::TAB2 . '],' . PHP_EOL;

                    return $carry;
                });

                return $carry;
            }) . '    ]';
            $listenersUseStatements = array_reduce(array_keys($eventListeners), function ($carry, $item) {
                $carry .= 'use App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Listeners\\' . $item . ';' . PHP_EOL;

                return $carry;
            });

            $eventsUseStatements = array_map(function ($listeners, $listener) {
                return array_map(fn ($event) => 'use App\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\Events\\' . $event . ';', $listeners);
            }, $eventListeners, array_keys($eventListeners));
            $eventsUseStatements = array_reduce($eventsUseStatements, static function ($carry, $item) {
                $carry .= array_reduce(array_keys($item), static function ($carry, $key) use ($item) {
                    $carry .= $item[$key] . PHP_EOL;

                    return $carry;
                });

                return $carry;
            });
        }

        $useStatements = $eventsUseStatements . $listenersUseStatements;

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
                'event-listeners' => $eventListenersString,
                'use-statements' => $useStatements,
                'event-service-provider' => $eventServiceProvider,
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }

    /**
     * Get the default file name for this component to be generated.
     */
    public function getDefaultFileName(): string
    {
        return 'MainServiceProvider';
    }
}
