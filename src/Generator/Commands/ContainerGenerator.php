<?php

namespace Apiato\Generator\Commands;

use Apiato\Generator\Generator;
use Apiato\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

final class ContainerGenerator extends Generator implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
        ['ui', null, InputOption::VALUE_OPTIONAL, 'The user-interface to generate the Controller for.'],
        ['events', null, InputOption::VALUE_OPTIONAL, 'Generate Events for this Container?'],
        ['listeners', null, InputOption::VALUE_OPTIONAL, 'Generate Event Listeners for Events of this Container?'],
        ['tests', null, InputOption::VALUE_OPTIONAL, 'Generate Tests for this Container?'],
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:make:container';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Container for apiato from scratch';
    /**
     * The type of file being generated.
     */
    protected string $fileType = 'Composer';
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
        $ui = Str::lower($this->checkParameterOrChoice('ui', 'Select the UI for this container', ['API', 'WEB', 'BOTH'], 0));
        $generateEvents = $this->checkParameterOrConfirm('events', 'Do you want to generate the corresponding CRUD Events for this Container?', false);
        $generateListeners = false;
        if ($generateEvents) {
            $generateListeners = $this->checkParameterOrConfirm('listeners', 'Do you want to generate the corresponding Event Listeners for this Events?', false);
        }
        $generateTests = $this->checkParameterOrConfirm('tests', 'Do you want to generate the corresponding Tests for this Container?', true);
        if ($generateTests) {
            $this->call('apiato:make:test:testcase', [
                '--section' => $this->sectionName,
                '--container' => $this->containerName,
                '--file' => 'TestCase',
                '--type' => 'container',
            ]);
            $this->call('apiato:make:test:testcase', [
                '--section' => $this->sectionName,
                '--container' => $this->containerName,
                '--file' => 'TestCase',
                '--type' => 'unit',
            ]);
            $this->call('apiato:make:test:testcase', [
                '--section' => $this->sectionName,
                '--container' => $this->containerName,
                '--file' => 'TestCase',
                '--type' => 'functional',
            ]);
            // $this->call('apiato:make:test:testcase', [
            //     '--section' => $this->sectionName,
            //     '--container' => $this->containerName,
            //     '--file' => 'TestCase',
            //     '--type' => 'e2e',
            // ]);
            $this->call('apiato:make:test:testcase', [
                '--section' => $this->sectionName,
                '--container' => $this->containerName,
                '--file' => 'TestCase',
                '--type' => 'api',
            ]);
            // $this->call('apiato:make:test:testcase', [
            //     '--section' => $this->sectionName,
            //     '--container' => $this->containerName,
            //     '--file' => 'TestCase',
            //     '--type' => 'cli',
            // ]);
            // $this->call('apiato:make:test:testcase', [
            //     '--section' => $this->sectionName,
            //     '--container' => $this->containerName,
            //     '--file' => 'TestCase',
            //     '--type' => 'web',
            // ]);
        }

        $containerName = $this->containerName;
        $_containerName = Str::lower($this->containerName);
        $sectionName = $this->sectionName;
        $_sectionName = Str::lower($this->sectionName);

        if ('api' === $ui || 'both' === $ui) {
            $this->call('apiato:make:container:api', [
                '--section' => $sectionName,
                '--container' => $containerName,
                '--file' => 'composer',
                '--events' => $generateEvents,
                '--listeners' => $generateListeners,
                '--tests' => $generateTests,
                '--maincalled' => true,
            ]);
        }

        if ('web' === $ui || 'both' === $ui) {
            $this->call('apiato:make:container:web', [
                '--section' => $sectionName,
                '--container' => $containerName,
                '--file' => 'composer',
                '--maincalled' => true,
            ]);
        }

        $this->printInfoMessage('Generating Composer File');

        return [
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
    }

    /**
     * Get the default file name for this component to be generated.
     */
    public function getDefaultFileName(): string
    {
        return 'composer';
    }

    public function getDefaultFileExtension(): string
    {
        return 'json';
    }
}
