<?php

declare(strict_types=1);

namespace Apiato\Generator\Commands;

use Apiato\Generator\Generator;
use Apiato\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

final class ServiceProviderGenerator extends Generator implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
        ['stub', null, InputOption::VALUE_OPTIONAL, 'The stub file to load for this generator.'],
    ];

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:make:provider';

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

    public function getUserInputs(): null|array
    {
        $stub = $this->option('stub');

        if (!$stub) {
            $stub = $this->checkParameterOrChoice(
                'stub',
                'Select the Stub you want to load',
                ['ServiceProvider', 'EventServiceProvider'],
                0,
            );

            $stub = match ($stub) {
                'EventServiceProvider' => 'event-service-provider',
                default                => 'service-provider',
            };
        }

        $this->stubName = \sprintf('providers/%s.stub', $stub);

        return [
            'path-parameters' => [
                'section-name'   => $this->sectionName,
                'container-name' => $this->containerName,
            ],
            'stub-parameters' => [
                '_section-name'   => Str::lower($this->sectionName),
                'section-name'    => $this->sectionName,
                '_container-name' => Str::lower($this->containerName),
                'container-name'  => $this->containerName,
                'class-name'      => $this->fileName,
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }

    /**
     * Get the default file name for this component to be generated.
     */
    #[\Override]
    public function getDefaultFileName(): string
    {
        return 'ServiceProvider';
    }
}
