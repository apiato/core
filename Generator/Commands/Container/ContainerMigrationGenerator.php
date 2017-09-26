<?php

namespace Apiato\Core\Generator\Commands\Container;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ContainerMigrationGenerator
 *
 * @author  Johannes Schobel <johannes.schobel@googlemail.com>
 */
class ContainerMigrationGenerator extends GeneratorCommand implements ComponentsGenerator
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:container-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an empty migration file for a Container';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $fileType = 'Migration';

    /**
     * The structure of the file path.
     *
     * @var  string
     */
    protected $pathStructure = '{container-name}/Data/Migrations/*';

    /**
     * The structure of the file name.
     *
     * @var  string
     */
    protected $nameStructure = '{date}_{file-name}';

    /**
     * The name of the stub file.
     *
     * @var  string
     */
    protected $stubName = 'container/migration.stub';

    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     *
     * @var  array
     */
    public $inputs = [
        ['tablename', null, InputOption::VALUE_OPTIONAL, 'The name for the database table'],
    ];

    /**
     * urn mixed|void
     */
    public function getUserInputs()
    {
        $tablename = Str::lower($this->checkParameterOrAsk('tablename', 'Enter the name of the database table'));

        return [
            'path-parameters' => [
                'container-name' => $this->containerName,
            ],
            'stub-parameters' => [
                '_container-name' => Str::lower($this->containerName),
                'container-name' => $this->containerName,
                'table-name' => $tablename
            ],
            'file-parameters' => [
                'date'      => Carbon::now()->format('Y_m_d_His'),
                'file-name' => $this->fileName,
            ],
        ];
    }

    /**
     * Get the default file name for this component to be generated
     *
     * @return string
     */
    public function getDefaultFileName()
    {
        $date = Carbon::now()->format('Y_m_d_His');
        return $date . '_' . 'setup_' . Str::lower($this->containerName) . '_tables';
    }
}
