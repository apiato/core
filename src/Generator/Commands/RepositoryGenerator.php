<?php

declare(strict_types=1);

namespace Apiato\Generator\Commands;

use Apiato\Generator\Generator;
use Apiato\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

final class RepositoryGenerator extends Generator implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
        ['model', null, InputOption::VALUE_OPTIONAL, 'The model to generate this Factory for'],
    ];

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Repository class';

    /**
     * The type of class being generated.
     */
    protected string $fileType = 'Repository';

    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/Data/Repositories/*';

    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{file-name}';

    /**
     * The name of the stub file.
     */
    protected string $stubName = 'repository.stub';

    public function getUserInputs(): null|array
    {
        $model = $this->checkParameterOrAsk('model', 'Enter the name of the Model to generate this Repository for');

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
                'model'           => $model,
                '_model'          => Str::lower($model),
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }
}
