<?php

declare(strict_types=1);

namespace Apiato\Generator\Commands;

use Apiato\Generator\Generator;
use Apiato\Generator\Interfaces\ComponentsGenerator;
use Carbon\Carbon;
use Illuminate\Support\Str;

final class SeederGenerator extends Generator implements ComponentsGenerator
{
    /**
     * @var string
     */
    public const FORMAT_TIME = 'Y_m_d_His';

    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
    ];

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:make:seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Seeder class';

    /**
     * The type of class being generated.
     */
    protected string $fileType = 'Seeder';

    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/Data/Seeders/*';

    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{file-name}';

    /**
     * The name of the stub file.
     */
    protected string $stubName = 'seeder.stub';

    private ?string $fileParametersDate = null;

    public function getUserInputs(): null|array
    {
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
        return \sprintf('Order_%s_%sSeeder', $this->getDate(), $this->containerName);
    }

    private function getDate(): string
    {
        if ($this->fileParametersDate === null) {
            $this->fileParametersDate = Carbon::now()->format(self::FORMAT_TIME);
        }

        return $this->fileParametersDate;
    }
}
