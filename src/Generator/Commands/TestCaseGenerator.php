<?php

namespace Apiato\Generator\Commands;

use Apiato\Generator\Generator;
use Apiato\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

final class TestCaseGenerator extends Generator implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
        ['type', null, InputOption::VALUE_OPTIONAL, 'The TestCase type.'],
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:make:test:testcase';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the TestCase file.';
    /**
     * The type of class being generated.
     */
    protected string $fileType = 'TestCase';
    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/Tests/*';
    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{file-name}';
    /**
     * The name of the stub file.
     */
    protected string $stubName = 'tests/testcase/generic.stub';

    public function getUserInputs(): array|null
    {
        $type = Str::lower($this->checkParameterOrChoice('type', 'Select the TestCase type', ['Container', 'Unit', 'Functional', 'E2E', 'API', 'CLI', 'WEB'], 0));

        $this->stubName = 'tests/testcase/' . $type . '.stub';
        if ('e2e' === $type) {
            $this->fileName = Str::upper($type) . $this->fileName;
        } else {
            $this->fileName = Str::ucfirst($type) . $this->fileName;
        }
        if ('api' === $type || 'cli' === $type) {
            $this->pathStructure = '{section-name}/{container-name}/Tests/Functional/*';
        }
        if ('web' === $type) {
            $this->pathStructure = '{section-name}/{container-name}/Tests/E2E/*';
        }

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
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }

    public function getDefaultFileName(): string
    {
        return 'TestCase';
    }
}
