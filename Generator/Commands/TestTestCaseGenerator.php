<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class TestTestCaseGenerator extends GeneratorCommand implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     *
     * @var  array
     */
    public $inputs = [
        ['ui', null, InputOption::VALUE_OPTIONAL, 'The user-interface to generate the TestCase for.'],
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:generate:test:testcase';
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

    /**
     * @return array
     */
    public function getUserInputs()
    {
        // We manually set the filename to TestCase as this is the preferred name within apiato
        $this->fileName = 'TestCase';

        $ui = Str::lower($this->checkParameterOrChoice('ui', 'Select the UI for the controller', ['Generic', 'API', 'WEB', 'CLI'], 0));

        // We need to generate the generic testcase first!
        if ($ui != 'generic') {
            $this->call('apiato:generate:test:testcase', [
                '--section' => $this->sectionName,
                '--container' => $this->containerName,
                '--file' => 'TestCase',
                '--ui' => 'generic',
            ]);

            // However, as this generator here is NOT the one for the generic TestCase, we need to prepend the UI before
            // this results in something like ApiTestCase
            $this->fileName = Str::ucfirst($ui) . $this->fileName;
        }

        $this->stubName = 'tests/testcase/' . $ui . '.stub';

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
