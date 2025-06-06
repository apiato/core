<?php

namespace Apiato\Generator\Commands;

use Apiato\Generator\Generator;
use Apiato\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

final class MailGenerator extends Generator implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
        ['view', null, InputOption::VALUE_OPTIONAL, 'The name of the view (blade template) to be loaded.'],
        ['subject', null, InputOption::VALUE_OPTIONAL, 'The subject of the email.'],
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:make:mail';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Mail class';
    /**
     * The type of class being generated.
     */
    protected string $fileType = 'Mail';
    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/Mails/*';
    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{file-name}';
    /**
     * The name of the stub file.
     */
    protected string $stubName = 'mail.stub';

    public function getUserInputs(): array|null
    {
        $view = $this->checkParameterOrAsk('view', 'Enter the name of the view to be loaded when sending this Mail', '');
        $subject = $this->checkParameterOrAsk('subject', "What's the the subject this Mail?", '');

        return [
            'path-parameters' => [
                'section-name' => $this->sectionName,
                'container-name' => $this->containerName,
            ],
            'stub-parameters' => [
                '_section-name' => Str::lower($this->sectionName),
                'section-name' => $this->sectionName,
                'sectionName' => Str::camel($this->sectionName),
                '_container-name' => Str::lower($this->containerName),
                'container-name' => $this->containerName,
                'containerName' => Str::camel($this->containerName),
                'class-name' => $this->fileName,
                'view' => $view,
                'subject' => $subject,
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }

    public function getDefaultFileName(): string
    {
        return 'DefaultMail';
    }
}
