<?php

namespace Apiato\Core\Generator;

use Apiato\Core\Foundation\Facades\Apiato;
use Apiato\Core\Generator\Traits\ConsoleCommandArgumentsTrait;
use Apiato\Core\Generator\Traits\ConsoleInputTrait;
use Apiato\Core\Generator\Traits\ConsoleOutputTrait;
use Apiato\Core\Generator\Traits\FileSystemTrait;
use Apiato\Core\Generator\Traits\FormatterTrait;
use Apiato\Core\Generator\Traits\HasTestTrait;
use Apiato\Core\Generator\Traits\SuggestionHelperTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

abstract class GeneratorCommand extends Command
{
    use ConsoleInputTrait;
    use ConsoleOutputTrait;
    use FileSystemTrait;
    use FormatterTrait;
    use ConsoleCommandArgumentsTrait;
    use SuggestionHelperTrait;

    protected string|null $sectionName = null;

    protected string|null $containerName = null;

    protected bool|null $test = null;

    public function __construct()
    {
        $this->name = $this->getCommandName();
        $this->description = $this->getCommandDescription();
        parent::__construct();
    }

    abstract public static function getCommandName(): string;

    abstract public static function getCommandDescription(): string;

    public function handle(): void
    {
        $this->setOptions();
    }

    public function runGeneratorCommand(string $commandClass, array $arguments = [], bool $silent = false): void
    {
        $commandInstance = app($commandClass);
        if (!$commandInstance instanceof GeneratorCommand) {
            throw new \RuntimeException('The command must be an instance of ' . GeneratorCommand::class);
        }

        if ($silent) {
            $this->callSilent($commandInstance::getCommandName(), $arguments);
        } else {
            $this->call($commandInstance::getCommandName(), $arguments);
        }
    }

    protected function setOptions(): void
    {
        $optionsFromConfig = $this->readYamlConfig(filePath: base_path() . '/code-generator-options.yaml', default: []);

        foreach ($optionsFromConfig as $key => $value) {
            //  Do not override the option if it was already set via command line
            if ($this->hasOption($key) && null === $this->option($key)) {
                $this->input->setOption($key, $value);
            }
        }
    }

    protected function askSection(): void
    {
        if ($this->sectionName) {
            return;
        }

        $input = $this->checkParameterOrAskTextSuggested(
            param: 'section',
            label: 'Select the section:',
            suggestions: Apiato::getSectionNames(),
        );

        $this->sectionName = Str::ucfirst($input);
        $this->sectionName = $this->removeSpecialChars($this->sectionName);
    }

    protected function askContainer(): void
    {
        if ($this->containerName) {
            return;
        }

        $input = $this->checkParameterOrAskTextSuggested(
            param: 'container',
            label: 'Select the container:',
            suggestions: Apiato::getSectionContainerNames($this->sectionName),
        );

        $this->containerName = Str::ucfirst($input);
        $this->containerName = $this->removeSpecialChars($this->containerName);
    }

    protected function askForTest(): void
    {
        if ($this->test || !$this->isTestable()) {
            return;
        }

        $this->test = $this->checkParameterOrConfirm(
            param: 'test',
            label: 'Do you want to create a test?',
            default: true,
            hint: 'This will create a test file for the ' . $this->getFileType(),
            required: false,
        );
    }

    protected function isTestable(): bool
    {
        return in_array(HasTestTrait::class, class_uses_recursive($this));
    }

    abstract protected function askCustomInputs(): void;

    abstract protected function runGeneratorCommands(): void;
}
