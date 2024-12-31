<?php

namespace Apiato\Generator\Commands;

use Apiato\Generator\Generator;
use Apiato\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class TransformerGenerator extends Generator implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
        ['model', null, InputOption::VALUE_OPTIONAL, 'The model to generate this Transformer for'],
        ['full', null, InputOption::VALUE_OPTIONAL, 'Generate a Transformer with all fields of the model'],
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:generate:transformer';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Transformer class for a given Model';
    /**
     * The type of class being generated.
     */
    protected string $fileType = 'Transformer';
    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/UI/API/Transformers/*';
    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{file-name}';
    /**
     * The name of the stub file.
     */
    protected string $stubName = 'transformer.stub';

    public function getUserInputs(): array|null
    {
        $model = $this->checkParameterOrAsk('model', 'Enter the name of the Model to generate this Transformer for');
        $full = $this->checkParameterOrConfirm('full', 'Generate a Transformer with all fields', false);

        $attributes = $this->getListOfAllAttributes($full, $model);

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
                'model' => $model,
                '_model' => Str::lower($model),
                'attributes' => $attributes,
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }

    private function getListOfAllAttributes($full, $model): string
    {
        $indent = str_repeat(' ', 12);
        $_model = Str::lower($model);
        $fields = [
            'object' => '$' . $_model . '->getResourceKey()',
        ];

        if ($full) {
            $obj = 'App\\Containers\\' . $this->sectionName . '\\' . $this->containerName . '\\Models\\' . $model;
            $obj = new $obj();
            $columns = Schema::getColumnListing($obj->getTable());

            foreach ($columns as $column) {
                if (in_array($column, $obj->getHidden(), false)) {
                    // Skip all hidden fields of respective model
                    continue;
                }

                $fields[$column] = '$' . $_model . '->' . $column;
            }
        }

        $fields = array_merge($fields, [
            'id' => '$' . $_model . '->getHashedKey()',
        ]);

        $attributes = '';
        foreach ($fields as $key => $value) {
            $attributes .= $indent . "'$key' => $value," . $this->getEndOfLine($key, $fields);
        }

        return $attributes;
    }

    private function getEndOfLine(string $currentKey, array $fields): string
    {
        $keys = array_keys($fields);
        $lastKey = end($keys);

        return $currentKey == $lastKey ? '' : PHP_EOL;
    }
}
