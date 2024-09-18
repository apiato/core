<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\CompositeGeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class EndpointGenerator extends CompositeGeneratorCommand
{
    protected string $feature;
    protected string $model;
    protected string $ui;
    protected string $stub;

    public static function getCommandName(): string
    {
        return 'apiato:make:endpoint';
    }

    public static function getCommandDescription(): string
    {
        return 'Create an Endpoint for a Container';
    }

    protected static function getCustomCommandArguments(): array
    {
        return [
            ['feature', null, InputOption::VALUE_OPTIONAL, 'The feature name of the endpoint. (E.g. CreateUser, LinkPostToBlog, DeleteAllPosts, ...)'],
            ['model', null, InputOption::VALUE_OPTIONAL, 'The model this endpoint is for.'],
            ['ui', null, InputOption::VALUE_OPTIONAL, 'The UI of the endpoint. (API, WEB)'],
            ['stub', null, InputOption::VALUE_OPTIONAL, 'The stub file to load for this endpoint.'],
        ];
    }

    protected function askCustomInputs(): void
    {
        $this->feature = $this->checkParameterOrAskText(
            param: 'feature',
            label: 'Enter the feature name:',
            hint: 'E.g. CreateUser, LinkPostToBlog, DeleteAllPosts, ...',
        );
        $this->model = $this->checkParameterOrAskTextSuggested(
            param: 'model',
            label: 'Enter the name of the Model:',
            default: $this->containerName,
            suggestions: $this->getModelsList(
                section: $this->sectionName,
                container: $this->containerName,
                removeModelPostFix: true,
            ),
        );
        $this->ui = $this->checkParameterOrSelect(
            param: 'ui',
            label: 'Select the UI of the endpoint:',
            options: ['API', 'WEB'],
            default: 'API',
        );
        $this->stub = $this->checkParameterOrSelect(
            param: 'stub',
            label: 'Select the endpoint type:',
            options: [
                'generic' => 'Generic',
                'list' => 'List',
                'find' => 'Find',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
            ],
            default: 'find',
            hint: 'Different types of endpoints have different default behaviors.',
        );
    }

    protected function runGeneratorCommands(): void
    {
        $featureCamelCase = lcfirst($this->feature);
        $featurePascalCase = ucfirst($this->feature);

        $this->runGeneratorCommand(RouteGenerator::class, [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
            '--file' => $featurePascalCase,
            '--ui' => $this->ui,
            '--controller' => $featurePascalCase . 'Controller',
            '--test' => $this->test,
        ]);
        $this->runGeneratorCommand(ActionGenerator::class, [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
            '--file' => $featurePascalCase . 'Action',
            '--model' => $this->model,
            '--stub' => $this->stub,
            '--ui' => $this->ui,
            '--test' => $this->test,
        ]);
        $this->runGeneratorCommand(ControllerGenerator::class, [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
            '--file' => $featurePascalCase . 'Controller',
            '--stub' => $this->stub,
            '--test' => $this->test,
        ]);
        $this->runGeneratorCommand(RequestGenerator::class, [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
            '--file' => $featurePascalCase . 'Request',
            '--model' => $this->model,
            '--stub' => $this->stub,
            '--test' => $this->test,
        ]);
        $this->runGeneratorCommand(PolicyGenerator::class, [
            '--section' => $this->sectionName,
            '--container' => $this->containerName,
            '--file' => $this->model . 'Policy',
            '--method' => $featureCamelCase,
            '--test' => $this->test,
        ]);
    }
}
