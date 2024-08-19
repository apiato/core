<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\GeneratorCommand;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ActionGenerator extends GeneratorCommand
{
    private string $model;

    private string $ui;

    private string $stub;

    public static function getCommandName(): string
    {
        return 'apiato:generate:action';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Action file for a Container';
    }

    public static function getFileType(): string
    {
        return 'action';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
            ['model', null, InputOption::VALUE_OPTIONAL, 'The model this action is for.'],
            ['stub', null, InputOption::VALUE_OPTIONAL, 'The stub file to load for this generator.'],
            ['ui', null, InputOption::VALUE_OPTIONAL, 'The user-interface to generate the Action for.'],
        ];
    }

    public function askCustomInputs(): void
    {
        $this->model = $this->checkParameterOrAskText(
            param: 'model',
            label: 'Enter the name of the model this action is for:',
            default: $this->containerName,
        );
        $this->ui = $this->checkParameterOrSelect(
            param: 'ui',
            label: 'Which UI is this Action for?',
            options: ['API', 'WEB'],
            default: 'API',
            hint: 'Different UIs have different request/response formats.',
        );
        $this->stub = $this->checkParameterOrSelect(
            param: 'stub',
            label: 'Select the action type:',
            options: [
                'generic' => 'Generic',
                'list' => 'List',
                'find' => 'Find',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
            ],
            default: 'generic',
            hint: 'Different types of actions have different default behaviors.',
        );
    }

    protected function getFilePath(): string
    {
        return "{$this->sectionName}/{$this->containerName}/Actions/{$this->fileName}.php";
    }

    protected function getStubFileName(): string
    {
        return "actions/{$this->stub}.stub";
    }

    protected function getStubParameters(): array
    {
        return [
            '_section-name' => Str::lower($this->sectionName),
            'section-name' => $this->sectionName,
            '_container-name' => Str::lower($this->containerName),
            'container-name' => $this->containerName,
            'class-name' => $this->fileName,
            'model' => $this->model,
            'models' => Pluralizer::plural($this->model),
            'ui' => $this->ui,
        ];
    }
}
