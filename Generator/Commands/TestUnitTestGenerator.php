<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;

class TestUnitTestGenerator extends GeneratorCommand implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     *
     * @var  array
     */
    public $inputs = [
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:generate:test:unit';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Unit Test file.';
    /**
     * The type of class being generated.
     */
    protected string $fileType = 'Unit Test';
    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/Tests/Unit/*';
    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{file-name}';
    /**
     * The name of the stub file.
     */
    protected string $stubName = 'tests/unit/general.stub';

    /**
     * @return array
     */
    public function getUserInputs()
    {
        // We need to generate the TestCase class before
        $this->call('apiato:generate:test:testcase', [
            '--container' => $this->containerName,
            '--file' => 'TestCase',
            '--ui' => 'generic',
        ]);

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
        return 'DefaultUnitTest';
    }
}

